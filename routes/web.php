<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/set-api-key', function () {
    if (session('api_key')) {
        return redirect('/')->with('info', 'API key already set.');
    }

    return view('set-api-key');
});

Route::post('/set-api-key', function (Request $request) {
    $request->validate([
        'api_key' => 'required|string',
    ]);
    $request->session()->put('api_key', $request->input('api_key'));
    $request->session()->put('expires_at', now()->addDays(30));

    return redirect('/')->with('success', 'API key set successfully.');
});

Route::get('/unset-api-key', function () {
    session()->flush();

    return redirect('/set-api-key')->with('success', 'API key unset successfully.');
});

Route::view('/', 'index')->middleware('session.api_key');

Route::get('/image', function () {
    $prompt = file_get_contents(__DIR__ . '/../resources/Prompt.md');
    $images = glob(__DIR__ . '/../resources/images/*.png');

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
})->middleware('session.api_key');

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
