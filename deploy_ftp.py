import ftplib
import os

# Configuration (Update these placeholders with your real credentials)
FTP_HOST = "145.79.212.119" # e.g. 145.79.212.119
FTP_USER = "u445085246.trishautsav.in"
FTP_PASS = "Codeforthelife@99"
FTP_PORT = 21
REMOTE_DIR = "public_html"
LOCAL_DIR = "/Users/macbookpro/Developement/FestiveTreat"

def upload_dir(ftp, local_path, remote_path):
    print(f"Entering directory: {remote_path}")
    try:
        ftp.cwd(remote_path)
    except ftplib.error_perm:
        try:
            ftp.mkd(remote_path)
            ftp.cwd(remote_path)
        except Exception as e:
            print(f"Error creating/changing to directory {remote_path}: {e}")
            return

    for item in os.listdir(local_path):
        # Skip hidden files EXCEPT .env
        if (item.startswith('.') and item != '.env') or item == 'node_modules':
            continue
            
        local_item = os.path.join(local_path, item)
        
        if os.path.isfile(local_item):
            # Only upload specific safe/code files if desired, or all files.
            # Using binary mode for all files.
            try:
                with open(local_item, 'rb') as f:
                    print(f"  Uploading {item}...")
                    ftp.storbinary(f'STOR {item}', f)
            except Exception as e:
                print(f"Failed to upload {item}: {e}")
                
        elif os.path.isdir(local_item):
            upload_dir(ftp, local_item, item)
            ftp.cwd('..')

def main():
    print(f"Connecting to {FTP_HOST}:{FTP_PORT}...")
    try:
        ftp = ftplib.FTP()
        ftp.connect(FTP_HOST, FTP_PORT)
        ftp.login(FTP_USER, FTP_PASS)
        print("Login successful.")
        
        # Change to root directory if needed, then upload
        upload_dir(ftp, LOCAL_DIR, REMOTE_DIR)
        
        ftp.quit()
        print("Deployment complete!")
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    main()
