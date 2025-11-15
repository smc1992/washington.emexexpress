#!/bin/bash

# Washington Airfreight Landing Page Docker Build Script
# Builds and pushes Docker image for Coolify deployment

set -e

# Configuration
DOCKER_REGISTRY="smc1992"
IMAGE_NAME="washington-airfreight-landing"
VERSION=${1:-latest}

echo "🇺🇸 Building Washington Airfreight Landing Page Docker Image"
echo "Registry: $DOCKER_REGISTRY"
echo "Image: $IMAGE_NAME"
echo "Version: $VERSION"
echo ""

# Build the Docker image
echo "📦 Building Docker image..."
docker build -t $DOCKER_REGISTRY/$IMAGE_NAME:$VERSION .
docker tag $DOCKER_REGISTRY/$IMAGE_NAME:$VERSION $DOCKER_REGISTRY/$IMAGE_NAME:latest

echo "✅ Build completed successfully!"
echo ""

# Push to registry (optional)
if [ "$2" = "push" ]; then
    echo "📤 Pushing to Docker registry..."
    docker push $DOCKER_REGISTRY/$IMAGE_NAME:$VERSION
    docker push $DOCKER_REGISTRY/$IMAGE_NAME:latest
    echo "✅ Push completed!"
fi

echo ""
echo "🎯 Washington Docker image ready for Coolify deployment!"
echo "📍 Image: $DOCKER_REGISTRY/$IMAGE_NAME:$VERSION"
echo "🌐 Domain: washington.emexexpress.de"
echo ""
echo "📋 Coolify Configuration:"
echo "   - Repository: $DOCKER_REGISTRY/$IMAGE_NAME"
echo "   - Tag: $VERSION"
echo "   - Port: 80"
echo "   - Environment Variables:"
echo "     * SMTP_HOST=mail.ionos.de"
echo "     * SMTP_PORT=587"
echo "     * SMTP_USER=ops@emexexpress.de"
echo "     * SMTP_PASS=your-password"
echo "     * ADMIN_EMAIL=ops@emexexpress.de"
echo "     * FROM_EMAIL=noreply@emexexpress.de"
