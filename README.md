# DentalGuard - Portable Installation

## Quick Start (Windows)

### First Time Setup:
1. Run `setup_portable.py` (double-click or run: `python setup_portable.py`)
2. Wait for installation to complete (~10 minutes)
3. Follow on-screen instructions

### Daily Use:
1. Double-click `START_PROJECT.bat`
2. Dashboard opens automatically at http://localhost:8000
3. To stop: Double-click `STOP_PROJECT.bat`

## System Requirements

- Windows 10/11
- Python 3.8+ (https://python.org)
- PHP 8.0+ (XAMPP recommended: https://apachefriends.org)
- Composer (https://getcomposer.org)
- 4GB RAM minimum (8GB+ recommended)
- 5GB free disk space

## Included Components

✅ AI Model (92.1% accuracy)
✅ Flask AI Server
✅ Laravel Backend
✅ Vue.js Frontend
✅ SQLite Database (portable)

## GPU Support

If you have NVIDIA GPU:
- PyTorch with CUDA will be installed automatically
- Significantly faster processing

Without GPU:
- Will use CPU (slower but functional)

## Troubleshooting

### "Python not found"
Install Python from python.org and check "Add to PATH"

### "Composer not found"
Install Composer from getcomposer.org

### Model not loading
Ensure `models/dental_v10_best.pt` exists

### Port already in use
Change port in `.env`:
- `AI_SERVER_PORT=5000` → `AI_SERVER_PORT=5001`
- Restart servers

## Support

Created by: [Your Team Name]
Project: DentalGuard AI Dental Analysis System
Date: 2026-01-21

## License

Educational/Academic Use Only
