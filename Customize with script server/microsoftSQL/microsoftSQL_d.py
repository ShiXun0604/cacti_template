from lib.msSQL import * 
from lib.db_query import *
from lib.queue_lib import *
import xml.etree.ElementTree as ET
import threading



# Static
TARGET_MSSQL_SETTING_LIST = {}
MONIT_INTERVAL = 10  # 偵測的秒數間隔,需要是300的因數
DATA_COUNT = 300 / MONIT_INTERVAL
WRITE_NIN = 4  # 寫入cacti database的時間
WRITE_NIN = (time.localtime().tm_min+1)%5  # 測試用

# 將config檔讀入
with open('C:\Apache24\htdocs\cacti\expertos\microsoftSQL_config.xml', 'rb') as f:
    # 讀進cacti mysql設定
    config = ET.fromstring(f.read().decode('utf-8'))
    DB_SETTING = {
        "host":config.find('dbSetting').find('host').text,
        "user":config.find('dbSetting').find('user').text, 
        "password":config.find('dbSetting').find('pwd').text,
        "database":config.find('dbSetting').find('database').text,
    }

    # 讀進待測microsoft SQL資料
    for ele in config.find('targetList'):
        svr_IP = ele.find('IP').text
        TARGET_MSSQL_SETTING_LIST[svr_IP] = {'server':svr_IP,
                                             'user' : ele.find('user').text,
                                             'password' : ele.find('pwd').text,}
    
    
    # 準備資料表
    IP = TARGET_MSSQL_SETTING_LIST.keys()  # IP列表
    for ip in IP:
        Data[ip] = {}
    for ip in Data.keys():
        Data[ip]['conn_count'] = safe_queue(maxLen=DATA_COUNT)
        Data[ip]['wait_count'] = safe_queue(maxLen=DATA_COUNT)
        Data[ip]['default_cpu'] = safe_queue(maxLen=DATA_COUNT)
        Data[ip]['internal_cpu'] = safe_queue(maxLen=DATA_COUNT)
        Data[ip]['delay_time'] = safe_queue(maxLen=DATA_COUNT)


# database資料檢查
DB_Conn_testing(DB_SETTING, detail=True)
with mysql.connector.connect(**DB_SETTING) as connector:
    cursor = connector.cursor()

    # 檢查DB監測對象是否跟config相同(雙向檢查) 
    cursor.execute("SELECT * FROM microsoftSQL;")
    db_IP = []
    
    for row in cursor.fetchall():
        db_ip = row[0]
        if db_ip not in IP:
            SQL_command = "DELETE from microsoftsql where IP='{}';".format(db_ip)
            cursor.execute(SQL_command)
            continue
        db_IP.append(db_ip)
    
    for ip in IP:
        if ip not in db_IP:
            SQL_command = "INSERT INTO microsoftsql (IP, conn_count, waiting_task_count) VALUES ('{}', 0, 0);".format(ip)
            cursor.execute(SQL_command)
    connector.commit()
    cursor.close()


# 開始程式迴圈
flag = True
while True:
    # 抓取分鐘數、秒數
    min = time.localtime().tm_min%5
    sec = time.localtime().tm_sec + min*60

    # 時間到則開始監測
    if sec%MONIT_INTERVAL == 0:
        # 刷新頁面
        os.system('cls' if os.name == 'nt' else 'clear')

        # 開始抓資料,一個IP一條thread
        thread_pool = []
        for ip in IP:
            thread_pool.append(threading.Thread(target=fetch_task, args=[TARGET_MSSQL_SETTING_LIST[ip], DATA_COUNT, ]))
        for i in thread_pool:
            i.start()
        for i in thread_pool:
            i.join()
        
        # 顯示結果
        result_info = '懿成資訊，Cacti監測microsoftSQL性能\n-------------------監測結果-------------------\n'
        for ip in IP:
            result_info += '{:<13}\t{:<15}{:<15}{:<15}{:<15}{:<15}\n'.format('msSQL server', 'conn_count', 'wait_count', 'default_cpu', 'internal_cpu', 'delay_time')
            conn_count = Data[ip]['conn_count'].calcu_average()
            wait_count = Data[ip]['wait_count'].calcu_average()
            default_cpu = Data[ip]['default_cpu'].calcu_average()
            internal_cpu = Data[ip]['internal_cpu'].calcu_average()
            delay_time = Data[ip]['delay_time'].calcu_average()
            result_info += "{:<13}\t{:<15.2f}{:<15.2f}{:<15.2f}{:<15.2f}{:<15.2f}\n".format(ip, conn_count, wait_count, default_cpu, internal_cpu, delay_time)
        result_info += '-------------------監測結果-------------------\n上次監測時間:'+time.asctime() 
        print(result_info)
    
    # 時間到將結果寫入database(每五分鐘一次)
    if min == WRITE_NIN -1:
        flag = True
    elif flag and (min == WRITE_NIN):
        flag = False
        # 寫入database
        with mysql.connector.connect(**DB_SETTING) as connector:
            cursor = connector.cursor()
            cursor.execute('use cacti_customize;')

            # 逐個ip寫入
            for ip in IP:
                # format內用顯示結果區段計算好的值來寫入, 記住set後面不跟逗號
                SQL_command = """
                    UPDATE microsoftsql 
                    SET conn_count = {:.2f}, 
                        waiting_task_count = {:.2f},
                        default_cpu = {:.2f},
                        internal_cpu = {:.2f},
                        delay_time = {:.2f}
                    WHERE IP = '{}';
                    """.format(conn_count, wait_count, default_cpu, internal_cpu, delay_time, ip)  
                cursor.execute(SQL_command)

            connector.commit()
            cursor.close()

    if sec %2 == 1:
        print(end=' ')
    print('程式運行中...')
    time.sleep(1)








