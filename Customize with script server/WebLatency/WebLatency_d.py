from lib.crawler import *
from lib.db_query import *
from lib.other import *
import threading



# Static
URL = [] 
KEYWD = []
TIMEOUT = []  # Second
DB_SETTING = {}
START_NIN = 1
# START_NIN = int(time.asctime().split(' ')[3].split(':')[1])%5  # for test


# 處理常數變數
with open('C:\Apache24\htdocs\cacti\expertos\WebLatency_config.xml', 'rb') as f:
    config = ET.fromstring(f.read().decode('utf-8'))
    DB_SETTING = {
        "host":config.find('dbSetting').find('host').text,
        "user":config.find('dbSetting').find('user').text, 
        "password":config.find('dbSetting').find('pwd').text,
        "database":config.find('dbSetting').find('database').text,
    }
for ele in config.find('targetList'):
    URL.append(ele.find('url').text)
    KEYWD.append(ele.find('keyWord').text)
    TIMEOUT.append(float(ele.find('timeoutSetting').text))
   
   


# database資料檢查
DB_Conn_testing(DB_SETTING, detail=True)
with mysql.connector.connect(**DB_SETTING) as connector:
    cursor = connector.cursor()

    # 檢查DB監測對象是否跟config相同(雙向檢查)
    cursor.execute("select url from weblatency;")
    db_URL = []
    for row in cursor.fetchall():
        db_url = row[0]
        if db_url not in URL:
            # 
            SQL_command = "DELETE from weblatency where url='{}';".format(db_url)
            cursor.execute(SQL_command)
            continue
        db_URL.append(db_url)

    for i in range(len(URL)):
        if URL[i] not in db_URL:
            SQL_command = "INSERT INTO weblatency (url, keyword, timeout) VALUES ('{}', '{}', '{}');".format(URL[i], KEYWD[i], str(TIMEOUT[i]))
            cursor.execute(SQL_command)
    connector.commit()
    cursor.close()


# thread pool
thread_pool = []
for i in range(len(URL)):
    thread_pool.append(threading.Thread(target=Monit, args=[KEYWD[i], URL[i], TIMEOUT[i]]))

# 開始抓取
for i in thread_pool:
    i.start()
# 等待抓取結束
for i in thread_pool:
    i.join()
# 紀錄監測時間
T = time.asctime()


while True:
    # 一直偵測時間,若分鐘值=(1,6,11,16,...)則監測一次數值
    min = time.localtime().tm_min
    if min%5 == START_NIN:
        
        # thread pool
        thread_pool = []
        for i in range(len(URL)):
            thread_pool.append(threading.Thread(target=Monit, args=[KEYWD[i], URL[i], TIMEOUT[i]]))

        # 開始抓取
        for i in thread_pool:
            i.start()
        # 等待抓取結束
        for i in thread_pool:
            i.join()
        # 紀錄監測時間
        T = time.asctime()
        
            
        # 寫入database
        with mysql.connector.connect(**DB_SETTING) as connector:
            cursor = connector.cursor()
            
            for url in URL:
                SQL_command = "UPDATE weblatency SET latency = {} WHERE url = '{}';".format(str(Latency[url]), url)
                cursor.execute(SQL_command)
            connector.commit()
            cursor.close()
            
        # 如果在一分鐘內完成則等待,避免外層if判斷時間問題
        min = time.localtime().tm_min
        if min%5 == START_NIN:
            wait_and_clean(wait_time=60, longStay_info=result_info(Latency, T))
    else:
        wait_and_clean(break_min=START_NIN, longStay_info=result_info(Latency, T))
