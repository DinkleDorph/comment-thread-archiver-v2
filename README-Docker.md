# Docker Shared Group Setup

This project uses a shared group approach that solves file permission issues for both development and production environments.

## How It Works

### Shared Group Approach
- **Shared Group**: Creates a group with your host GID that both www-data and your host user belong to
- **Group Permissions**: Files are owned by www-data but writable by the shared group
- **Apache as Root**: Apache runs normally without permission issues
- **File Permissions**: All files created by the container (artisan, npm, etc.) are immediately editable by your host user

### Both Development and Production
- **Standard Apache**: Runs as root with normal logging and configuration
- **Port Mapping**: 8080:80 for development, 80:80 for production
- **Unified Approach**: Same permission strategy works for all environments

## Usage

### Development (Default)
```bash
# Start development environment
docker compose up

# Build and start
docker compose up --build

# Run in background
docker compose up -d
```

### Production
```bash
# Start production environment
docker compose -f docker-compose.yml -f docker-compose.prod.yml up

# Build and start production
docker compose -f docker-compose.yml -f docker-compose.prod.yml up --build
```

## Environment Variables

Set these in your shell or `.env` file:

```bash
# Your host user ID and group ID (usually 1000:1000)
export UID=$(id -u)
export GID=$(id -g)
```

Or add to `.env`:
```
UID=1000
GID=1000
```

## Benefits

✅ **No file permission issues** - Files created by container are owned by your user  
✅ **No manual fixes needed** - npm install, artisan, editing all work perfectly  
✅ **Production ready** - Clean separation between dev and prod configurations  
✅ **Simple to use** - One command for dev, one for prod  
✅ **Follows Docker best practices** - User mapping for development environments  

## Troubleshooting

### Permission Denied Errors
If you get permission errors, ensure your UID/GID environment variables are set:
```bash
echo "UID: $(id -u), GID: $(id -g)"
```

### Port Conflicts
- Development uses port 8080 (non-privileged)
- Production uses port 80 (requires privileged container or reverse proxy)

### File Ownership Issues
In development, all files should be owned by your user. If not, rebuild the container:
```bash
docker compose down
docker compose build --no-cache
docker compose up
