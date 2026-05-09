import requests
import random
import time
import urllib3
from datetime import datetime

# Nonaktifkan peringatan SSL untuk self-signed certificate
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# URL endpoint API
API_URL = "https://127.0.0.1:8000/api/rpm-readings"

# Jumlah mesin
JUMLAH_MESIN = 10  # Dari MESIN-0 hingga MESIN-25

def kirim_data_mesin(rpm_id):
    """
    Membuat data acak dan mengirimkannya ke API untuk satu mesin.
    """
    # Hasilkan data acak untuk endpoint rpm-readings
    if random.random() < 0.2:
        rpm = 0
    else:
        rpm = round(random.uniform(500.0, 2500.0), 2)
        
    hm = round(random.uniform(100.0, 5000.0), 2)
    temp = round(random.uniform(30.0, 90.0), 2)
    humi = round(random.uniform(20.0, 95.0), 2)
    timestamp = datetime.now().isoformat()

    # Buat payload data
    payload = {
        "rpm_id": rpm_id,
        "rpm": rpm,
        "hm": hm,
        "temp": temp,
        "humi": humi,
        "timestamp": timestamp
    }

    try:
        # Kirim permintaan POST ke API
        headers = {
            "Accept": "application/json",
            "Content-Type": "application/json"
        }
        response = requests.post(API_URL, json=payload, headers=headers, timeout=15, verify=False)
        
        # Cetak status respons dan header untuk verifikasi server
        server_header = response.headers.get('Server', 'Unknown')
        print(f"[{datetime.now().strftime('%H:%M:%S')}] {rpm_id} -> "
              f"Status: {response.status_code}, Server: {server_header}, Response: {response.text}")
        if response.status_code != 201:
            print(f"Peringatan: push data {rpm_id} tidak sukses (expected 201).")

    except requests.exceptions.RequestException as e:
        print(f"Gagal mengirim data untuk {rpm_id}: {e}")

if __name__ == "__main__":
    print("Memulai pengiriman data dummy ke API...")
    
    while True:
        print(f"\n--- Mengirim data batch baru pada {datetime.now()} ---")
        # Ulangi untuk setiap mesin dari MESIN-0 hingga MESIN-25
        for i in range(JUMLAH_MESIN):
            nama_mesin = f"MESIN-{i}"
            kirim_data_mesin(nama_mesin)
            # Beri jeda singkat antar permintaan untuk tidak membebani server
            time.sleep(0.1)
            
        print(f"\nPengiriman batch selesai. Menunggu 30 detik...")
        time.sleep(30)
