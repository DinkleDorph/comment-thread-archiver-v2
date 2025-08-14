<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set error handling
if ($_ENV['ENV'] !== 'production') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
}

// Set session lifetime
ini_set('session.cookie_lifetime', 2592000); // 30 days
ini_set('session.gc_maxlifetime', 2592000);
session_start();

// Set up Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../Src/Views');
$twig = new \Twig\Environment($loader);

// ---------- Routes ----------

Flight::route('/', function () use ($twig) {
    echo $twig->render('index.twig', [
        'ENV' => $_ENV['ENV'],
        'OPENAI_API_KEY_SET' => true,
    ]);
});

Flight::route('/image', function () {
    $prompt = file_get_contents(__DIR__ . '/../Prompt.md');
    $images = glob(__DIR__ . '/../Src/*.png');

    // Step 1: Start conversation with prompt
    $responseData = sendOpenAiRequest([
        [
            "type" => "input_text",
            "text" => $prompt,
        ],
    ]);
    $responseId = $responseData['id'];

    // Step 2: Batch images in groups of 10
    $batches = array_chunk($images, 10);
    foreach ($batches as $batchIndex => $batch) {
        $content = [];

        foreach ($batch as $i => $image) {
            $imageData = base64_encode(file_get_contents($image));
            $mimeType = mime_content_type($image);

            $content[] = [
                "type" => "input_image",
                "image_url" => "data:$mimeType;base64,$imageData",
            ];

            $content[] = [
                "type" => "input_text",
                "text" => "Image " . ($i + 1 + ($batchIndex * 10)) . " of " . count($images),
            ];
        }

        try {
            $responseData = sendOpenAiRequest($content, $responseId);
        } catch (Error) {
            echo "<pre>";
            print_r($responseData['output'][0]['content'][0]['text']);
            echo "</pre>";
        }
        $responseId = $responseData['id'];
    }

    // Step 3: Final request to process everything
    $responseData = sendOpenAiRequest(
        [
            [
                "type" => "input_text",
                "text" => "Now process all images together.",
            ]
        ],
        $responseId,
        "json_object",
    );

    echo "<pre>";
    print_r($responseData['output'][0]['content'][0]['text']);
    echo "</pre>";
});

Flight::start();

function sendOpenAiRequest(
    array $content,
    ?string $responseId = null,
    ?string $responseFormatType = null,
): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/responses");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "model" => "gpt-4.1-nano",
        "input" => [
            [
                "role" => "user",
                "content" => $content,
            ],
        ],
        "previous_response_id" => $responseId,
        "text" => [
            "format" => [
                "type" => $responseFormatType ?? "text",
            ],
        ],
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $_ENV['OPENAI_API_KEY']
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Error(curl_error($ch));
    }

    curl_close($ch);

    $result = trim($result); // remove any accidental whitespace
    $response = json_decode($result, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Error("JSON parse error: " . json_last_error_msg() . "\nRaw response:\n" . $result);
    }

    if (isset($response['error'])) {
        throw new Error("OpenAI API error: " . $response['error']['message']);
    }

    return $response;
}
