import requests
import random
import time
from datetime import datetime

# URL endpoint API
API_URL = "http://localhost:8000/api/machine-readings"

# Jumlah mesin
JUMLAH_MESIN = 14  # Dari MESIN-0 hingga MESIN-25

def kirim_data_mesin(machine_id):
    """
    Membuat data acak dan mengirimkannya ke API untuk satu mesin.
    """
    # Hasilkan data acak
    # Sesekali beri nilai amp 0 (sekitar 20% kemungkinan)
    if random.random() < 0.2:
        amp = 0
    else:
        amp = round(random.uniform(0.5, 5.0), 2)
        
    hm = round(random.uniform(100.0, 500.0), 2)
    temp = round(random.uniform(30.0, 90.0), 2)
    moist = round(random.uniform(5.0, 20.0), 2)
    timestamp = datetime.now().isoformat()

    # Buat payload data
    payload = {
        "machine_id": machine_id,
        "amp": amp,
        "hm": hm,
        "temp": temp,
        "moist": moist,
        "timestamp": timestamp
    }

    try:
        # Kirim permintaan POST ke API
        response = requests.post(API_URL, json=payload)
        
        # Cetak status respons
        print(f"Mengirim data untuk {machine_id}: "
              f"Status {response.status_code} - Response: {response.text}")

    except requests.exceptions.RequestException as e:
        print(f"Gagal mengirim data untuk {machine_id}: {e}")

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
