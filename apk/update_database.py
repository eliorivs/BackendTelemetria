import requests
import sys
import logging
import pandas as pd


logger = logging.getLogger('')
logger.setLevel(logging.DEBUG)
fh = logging.FileHandler('update_info.log')
sh = logging.StreamHandler(sys.stdout)
formatter = logging.Formatter('[%(asctime)s] %(levelname)s [%(filename)s.%(funcName)s:%(lineno)d] %(message)s', datefmt='%a, %d %b %Y %H:%M:%S')
fh.setFormatter(formatter)
sh.setFormatter(formatter)
logger.addHandler(fh)
logger.addHandler(sh)

def ProcessData(url):
    try:
       
        resp = requests.get(url=url)
        data = resp.json() # Check the JSON Response Content documentation below
        lecturas = (data['lecturas'])
        df = pd.DataFrame.from_dict(lecturas)
        print(df)
        num_filas = df.shape[0]
        print(f"El DataFrame tiene {num_filas} filas")
        logger.info(f"Obtenida data de {num_filas} estaciones")
        logger.info("Conectado al servidor!")
    except:
        print ('error al conectar a URL')
        logger.critical("error al conectar a URL")

url = 'https://gpconsultores.cl/PDC_ONLINE/backend/update_table.php'
ProcessData(url)
