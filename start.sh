#!/bin/bash

# Set the port
export PORT=${PORT:-8000}

# Start the PHP built-in server
php -S 0.0.0.0:$PORT -t public/
