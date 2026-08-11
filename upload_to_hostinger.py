#!/usr/bin/env python3
"""
Trisha Utsav - Automated Hostinger Live Deployment Script
Uploads deploy_package.zip and deploy.php to Hostinger via FTPS/FTP
and triggers the automated server-side extractor.
"""

import os
import sys
import time
import zipfile
import ftplib
import urllib.request
import urllib.parse
import ssl

# ==========================================
# CONFIGURATION
# ==========================================
FTP_HOST = os.getenv('FTP_HOST', '145.79.212.119')
FTP_USER = os.getenv('FTP_USER', 'u445085246.trishautsav.in')
FTP_PASS = os.getenv('FTP_PASS', 'Codeforthelife@99')
FTP_PORT = int(os.getenv('FTP_PORT', 21))
REMOTE_DIR = os.getenv('REMOTE_DIR', 'public_html')

DEPLOY_URL = os.getenv('DEPLOY_URL', 'https://trishautsav.in/deploy.php')

FILES_TO_UPLOAD = ['deploy_package.zip', 'deploy.php']

PROJECT_ROOT = os.path.dirname(os.path.abspath(__file__))


def create_deploy_zip():
    """Zips the project files into deploy_package.zip"""
    zip_path = os.path.join(PROJECT_ROOT, 'deploy_package.zip')
    print("📦 Creating deployment zip package: deploy_package.zip ...")
    
    ignore_dirs = {'.git', 'node_modules', '.gemini', '.idea', '.vscode'}
    ignore_files = {'deploy_package.zip', '.DS_Store', 'upload_to_hostinger.py', 'u445085246_Trusha_Utsav.sql'}
    
    count = 0
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(PROJECT_ROOT):
            # Exclude ignored directories in-place
            dirs[:] = [d for d in dirs if d not in ignore_dirs]
            
            for file in files:
                if file in ignore_files or file.endswith('.pyc'):
                    continue
                full_path = os.path.join(root, file)
                rel_path = os.path.relpath(full_path, PROJECT_ROOT)
                zipf.write(full_path, rel_path)
                count += 1

    size_mb = os.path.getsize(zip_path) / (1024 * 1024)
    print(f"✅ Created deploy_package.zip containing {count} files ({size_mb:.2f} MB).\n")
    return zip_path


def upload_via_ftps():
    """Connects to Hostinger via FTPS/FTP and uploads deployment files"""
    print(f"📡 Connecting to Hostinger FTP server ({FTP_HOST}:{FTP_PORT})...")
    print(f"👤 Username: {FTP_USER}")

    # Attempt FTPS (FTP_TLS) first, fall back to plain FTP
    ftp_client = None
    use_tls = True

    try:
        context = ssl.create_default_context()
        context.check_hostname = False
        context.verify_mode = ssl.CERT_NONE
        
        ftps = ftplib.FTP_TLS(context=context)
        ftps.connect(FTP_HOST, FTP_PORT, timeout=30)
        ftps.login(FTP_USER, FTP_PASS)
        ftps.prot_p()
        ftps.set_pasv(True)
        ftp_client = ftps
        print("🔐 Connected securely via FTPS (Implicit/Explicit TLS).")
    except Exception as e:
        print(f"⚠️  FTPS Connection failed ({e}). Falling back to Standard FTP...")
        try:
            ftp = ftplib.FTP()
            ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
            ftp.login(FTP_USER, FTP_PASS)
            ftp.set_pasv(True)
            ftp_client = ftp
            use_tls = False
            print("🔓 Connected via Standard FTP.")
        except Exception as e2:
            print(f"❌ FTP Connection Failed: {e2}")
            print("\n💡 Tip: Verify network connection or Hostinger FTP status.")
            return False

    try:
        # Navigate to target directory
        if REMOTE_DIR:
            print(f"📂 Navigating to remote folder: {REMOTE_DIR}")
            try:
                ftp_client.cwd(REMOTE_DIR)
            except Exception:
                print(f"⚠️  Directory {REMOTE_DIR} not found or already in root.")

        # Upload files
        for filename in FILES_TO_UPLOAD:
            filepath = os.path.join(PROJECT_ROOT, filename)
            if not os.path.exists(filepath):
                print(f"⚠️  File {filename} missing locally, skipping.")
                continue

            file_size = os.path.getsize(filepath)
            file_size_mb = file_size / (1024 * 1024)
            print(f"🚀 Uploading {filename} ({file_size_mb:.2f} MB)...")

            start_time = time.time()
            uploaded_bytes = 0

            def callback(chunk):
                nonlocal uploaded_bytes
                uploaded_bytes += len(chunk)
                percent = (uploaded_bytes / file_size) * 100
                sys.stdout.write(f"\r   Progress: [{percent:5.1f}%] {uploaded_bytes}/{file_size} bytes")
                sys.stdout.flush()

            with open(filepath, 'rb') as f:
                ftp_client.storbinary(f"STOR {filename}", f, blocksize=65536, callback=callback)

            elapsed = time.time() - start_time
            speed = (file_size / (1024 * 1024)) / elapsed if elapsed > 0 else 0
            print(f"\n   ✅ Uploaded {filename} in {elapsed:.1f}s ({speed:.2f} MB/s)")

        ftp_client.quit()
        print("\n🎉 FTPS File Upload Completed Successfully!\n")
        return True

    except Exception as e:
        print(f"\n❌ Error during file upload: {e}")
        try:
            ftp_client.quit()
        except Exception:
            pass
        return False


def trigger_remote_deployment():
    """Triggers deploy.php via HTTP GET to extract zip and import database"""
    print(f"⚙️  Triggering live server-side extraction: {DEPLOY_URL}")
    req = urllib.request.Request(
        DEPLOY_URL,
        headers={
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        }
    )
    try:
        with urllib.request.urlopen(req, timeout=45) as response:
            html = response.read().decode('utf-8')
            if 'Production Deployment Ready!' in html or 'Successfully extracted' in html:
                print("✅ Live Deployment Extracted Successfully!")
                print("✨ Storefront is live!")
            else:
                print("ℹ️ Server responded to deploy.php.")
    except Exception as e:
        print(f"⚠️ Could not trigger deploy.php automatically ({e}).")
        print(f"👉 Please open {DEPLOY_URL} in your browser to complete setup.")


def main():
    print("=" * 60)
    print(" 🚀 TRISHA UTSAV - HOSTINGER LIVE DEPLOYMENT SCRIPT")
    print("=" * 60)
    
    # Step 1: Zip codebase
    create_deploy_zip()
    
    # Step 2: Upload via FTP
    success = upload_via_ftps()
    
    # Step 3: Trigger extraction
    if success:
        trigger_remote_deployment()
    else:
        print("\n❌ Deployment halted due to FTP connection issues.")
        print("💡 You can manually upload 'deploy_package.zip' via Hostinger File Manager.")

    print("\n" + "=" * 60)

if __name__ == '__main__':
    main()
