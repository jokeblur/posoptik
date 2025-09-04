#!/bin/bash

# Script untuk mengatasi konflik git di VPS
echo "=== Mengatasi Konflik Git di VPS ==="

# 1. Cek status git
echo "1. Checking git status..."
git status

# 2. Backup file yang berubah
echo "2. Creating backup of local changes..."
if [ -f "deploy_fix.sh" ]; then
    cp deploy_fix.sh deploy_fix.sh.backup
    echo "Backup created: deploy_fix.sh.backup"
fi

# 3. Stash perubahan local
echo "3. Stashing local changes..."
git stash push -m "Local changes before pull"

# 4. Pull perubahan dari remote
echo "4. Pulling changes from remote..."
git pull origin main

# 5. Cek apakah stash ada
echo "5. Checking stash..."
if git stash list | grep -q "Local changes before pull"; then
    echo "Stash found. Do you want to apply it? (y/n)"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        echo "Applying stash..."
        git stash pop
    else
        echo "Stash not applied. You can apply it later with: git stash pop"
    fi
else
    echo "No stash found."
fi

echo "=== Git conflict resolution completed ==="
echo "If you need the backup file, it's saved as deploy_fix.sh.backup"
