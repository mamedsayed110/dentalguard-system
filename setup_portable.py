"""
DentalGuard - Portable Setup Script
Automatically configures the project on any Windows machine
Run this ONCE on each new machine
"""

import os
import sys
import subprocess
import json
from pathlib import Path

def print_header(text):
    print("\n" + "="*70)
    print(f"  {text}")
    print("="*70 + "\n")

def run_command(cmd, description):
    """Run a command and return success status"""
    print(f"⏳ {description}...")
    try:
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
        if result.returncode == 0:
            print(f"✅ {description} - SUCCESS")
            return True
        else:
            print(f"❌ {description} - FAILED")
            print(f"   Error: {result.stderr}")
            return False
    except Exception as e:
        print(f"❌ {description} - ERROR: {e}")
        return False

def check_python():
    """Check if Python is installed"""
    print_header("Checking Python Installation")
    
    try:
        version = sys.version.split()[0]
        print(f"✅ Python {version} found")
        
        # Check version
        major, minor = map(int, version.split('.')[:2])
        if major == 3 and minor >= 8:
            print(f"✅ Python version is compatible (3.8+)")
            return True
        else:
            print(f"⚠️  Python 3.8+ required, you have {version}")
            return False
    except:
        print("❌ Python not found!")
        print("   Please install Python 3.8+ from python.org")
        return False

def check_php():
    """Check if PHP is installed"""
    print_header("Checking PHP Installation")
    
    result = subprocess.run(['php', '--version'], 
                          capture_output=True, text=True)
    if result.returncode == 0:
        version = result.stdout.split('\n')[0]
        print(f"✅ {version}")
        return True
    else:
        print("❌ PHP not found!")
        print("   Please install PHP 8.0+ (XAMPP recommended)")
        return False

def check_composer():
    """Check if Composer is installed"""
    print_header("Checking Composer Installation")
    
    result = subprocess.run(['composer', '--version'], 
                          capture_output=True, text=True)
    if result.returncode == 0:
        version = result.stdout.split('\n')[0]
        print(f"✅ {version}")
        return True
    else:
        print("❌ Composer not found!")
        print("   Please install from getcomposer.org")
        return False

def install_python_packages():
    """Install required Python packages"""
    print_header("Installing Python Packages")
    
    packages = [
        "ultralytics",
        "flask",
        "flask-cors",
        "opencv-python",
        "pillow",
        "numpy",
        "python-dotenv"
    ]
    
    print("📦 Installing packages (this may take 5-10 minutes)...")
    
    for package in packages:
        run_command(
            f"python -m pip install {package}",
            f"Installing {package}"
        )
    
    # Install PyTorch with CUDA (if available)
    print("\n🔥 Installing PyTorch with GPU support...")
    run_command(
        "python -m pip install torch torchvision --index-url https://download.pytorch.org/whl/cu118",
        "Installing PyTorch + CUDA"
    )

def install_composer_packages():
    """Install Laravel dependencies"""
    print_header("Installing Laravel Dependencies")
    
    if os.path.exists('composer.json'):
        run_command(
            "composer install --no-interaction",
            "Installing Composer packages"
        )
    else:
        print("⚠️  composer.json not found - skipping")

def setup_laravel():
    """Configure Laravel"""
    print_header("Configuring Laravel")
    
    # Copy .env.example to .env if not exists
    if not os.path.exists('.env'):
        if os.path.exists('.env.example'):
            import shutil
            shutil.copy('.env.example', '.env')
            print("✅ Created .env file")
        else:
            print("⚠️  .env.example not found")
    
    # Generate app key
    run_command("php artisan key:generate", "Generating app key")
    
    # Run migrations
    print("\n📊 Setting up database...")
    run_command("php artisan migrate:fresh", "Running migrations")
    
    # Clear caches
    run_command("php artisan config:clear", "Clearing config cache")
    run_command("php artisan cache:clear", "Clearing cache")

def create_directories():
    """Create necessary directories"""
    print_header("Creating Project Directories")
    
    dirs = [
        "models",
        "results",
        "datasets",
        "logs",
        "storage/app/public/xrays",
        "storage/app/public/results"
    ]
    
    for d in dirs:
        Path(d).mkdir(parents=True, exist_ok=True)
        print(f"✅ {d}/")
    
    # Create storage link
    run_command("php artisan storage:link", "Creating storage link")

def verify_model():
    """Check if model exists"""
    print_header("Verifying AI Model")
    
    model_path = "models/dental_v10_best.pt"
    if os.path.exists(model_path):
        size = os.path.getsize(model_path) / (1024*1024)
        print(f"✅ Model found: {model_path} ({size:.1f} MB)")
        return True
    else:
        print(f"❌ Model not found: {model_path}")
        print("   Please ensure the model file is in the models/ folder")
        return False

def test_gpu():
    """Test if GPU is available"""
    print_header("Testing GPU Availability")
    
    try:
        import torch
        if torch.cuda.is_available():
            gpu_name = torch.cuda.get_device_name(0)
            print(f"✅ GPU detected: {gpu_name}")
            print(f"   CUDA version: {torch.version.cuda}")
            return True
        else:
            print("⚠️  No GPU detected - will use CPU")
            print("   (Training/Inference will be slower)")
            return False
    except ImportError:
        print("⚠️  PyTorch not installed yet")
        return False

def create_run_scripts():
    """Create convenient run scripts"""
    print_header("Creating Run Scripts")
    
    # Windows batch script to start everything
    start_script = """@echo off
echo ================================================
echo   DentalGuard - Starting All Services
echo ================================================

echo.
echo [1/2] Starting AI Server...
start "AI Server" cmd /k "cd /d %~dp0 && python ai_server.py"

timeout /t 3 /nobreak >nul

echo [2/2] Starting Laravel Server...
start "Laravel Server" cmd /k "cd /d %~dp0 && php artisan serve"

echo.
echo ================================================
echo   All services started!
echo ================================================
echo.
echo   Dashboard: http://localhost:8000
echo   AI Server: http://localhost:5000
echo.
echo   Press any key to open dashboard...
pause >nul

start http://localhost:8000

exit
"""
    
    with open('START_PROJECT.bat', 'w') as f:
        f.write(start_script)
    print("✅ Created START_PROJECT.bat")
    
    # Stop script
    stop_script = """@echo off
echo Stopping all DentalGuard services...

taskkill /FI "WindowTitle eq AI Server*" /T /F >nul 2>&1
taskkill /FI "WindowTitle eq Laravel Server*" /T /F >nul 2>&1

echo.
echo ✅ All services stopped!
pause
"""
    
    with open('STOP_PROJECT.bat', 'w') as f:
        f.write(stop_script)
    print("✅ Created STOP_PROJECT.bat")

def main():
    """Main setup routine"""
    print("\n")
    print("🦷" * 35)
    print("\n    DENTALGUARD - PORTABLE SETUP")
    print("\n" + "🦷" * 35)
    print("\n")
    
    print("This script will configure DentalGuard on this machine.")
    print("Estimated time: 10-15 minutes")
    print("\n")
    
    input("Press Enter to continue...")
    
    # Step 1: Check prerequisites
    checks_passed = True
    checks_passed &= check_python()
    checks_passed &= check_php()
    
    if not checks_passed:
        print("\n❌ Some prerequisites are missing!")
        print("   Please install missing software and run again.")
        input("\nPress Enter to exit...")
        return
    
    # Step 2: Create directories
    create_directories()
    
    # Step 3: Install packages
    install_python_packages()
    install_composer_packages()
    
    # Step 4: Setup Laravel
    setup_laravel()
    
    # Step 5: Verify model
    verify_model()
    
    # Step 6: Test GPU
    test_gpu()
    
    # Step 7: Create run scripts
    create_run_scripts()
    
    # Final summary
    print_header("Setup Complete!")
    
    print("✅ Project is ready to use!")
    print("\n📋 Quick Start:")
    print("   1. Double-click START_PROJECT.bat")
    print("   2. Wait for both servers to start")
    print("   3. Dashboard will open automatically")
    print("\n⚠️  To stop all services:")
    print("   - Double-click STOP_PROJECT.bat")
    print("   - Or close both CMD windows")
    
    print("\n" + "="*70)
    input("\nPress Enter to exit...")

if __name__ == "__main__":
    main()