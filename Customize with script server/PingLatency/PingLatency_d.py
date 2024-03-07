import threading, socket, time
from lib.queue_lib import *
from lib.ping_lib import *
from lib.db_query import *



# Static
RESULT_COUNT = 30
AVERAGE_INTERVAL = 10
IP = []
with open('C:\Apache24\htdocs\cacti\expertos\PingLatency_config.xml', 'rb') as f:
    config = ET.fromstring(f.read().decode('utf-8'))
    DB_SETTING = {
        "host":config.find('dbSetting').find('host').text,
        "user":config.find('dbSetting').find('user').text, 
        "password":config.find('dbSetting').find('pwd').text,
        "database":config.find('dbSetting').find('database').text,
    }
    for ele in config.find('targetList'):
        IP.append(ele.find('IP').text)

# database資料檢查
DB_Conn_testing(DB_SETTING, detail=True)
with mysql.connector.connect(**DB_SETTING) as connector:
    cursor = connector.cursor()

    # 檢查DB監測對象是否跟config相同(雙向檢查) 
    cursor.execute("SELECT * FROM pinglatency;")
    db_IP = []
    for row in cursor.fetchall():
        db_ip = row[0]
        if db_ip not in IP:
            SQL_command = "DELETE from pinglatency where IP='{}';".format(db_ip)
            cursor.execute(SQL_command)
            continue
        db_IP.append(db_ip)
    
    for i in IP:
        if i not in db_IP:
            SQL_command = "INSERT INTO pinglatency (IP, latency, faliure) VALUES ('{}', 0, 0);".format(i)
            cursor.execute(SQL_command)
    connector.commit()
    cursor.close()
 
# 創建資源和鎖 
for i in range(len(IP)):
    ping_queue.append(Queue())
    lock.append(Lock())

result_info = ''
flag = False
while True:
    min = time.localtime().tm_min
    sec = time.localtime().tm_sec

    # 開始監測任務
    if sec%AVERAGE_INTERVAL == 0:
        # 刷新頁面
        os.system('cls' if os.name == 'nt' else 'clear')
        thread_pool = []
        for i in range(len(IP)):
            thread_pool.append(threading.Thread(target=ping_and_save,args=[IP[i], RESULT_COUNT, i]))
        for i in thread_pool:
            i.start()  # 所有線呈會在10秒內結束
        #for i in thread_pool:  
        #    i.join()

        # 顯示新的結果
        result_info = '懿成資訊，Cacti監測ping延遲(PingLatency)\n-------------------監測結果-------------------\n'
        for i in range(len(ping_queue)):
            lock[i].acquire()
            result_info += IP[i]+' : '+return_queue_contents(ping_queue[i])+'\n'
            lock[i].release()
        result_info += '-------------------監測結果-------------------\n上次監測時間:'+time.asctime() 
        print(result_info)

    # 寫入database
    if min%5 == 0:  # 分鐘數為1,6,11...時寫入database
        flag = True
    elif flag and min%5 == 1:
        flag = False
        # 寫入database
        for i in range(len(ping_queue)):
            # 讀取queue資料
            lock[i].acquire()
            average, err = calculate_average(ping_queue[i])
            lock[i].release()
            # 寫入database
            with mysql.connector.connect(**DB_SETTING) as connector:
                cursor = connector.cursor()
                SQL_command = "UPDATE pinglatency SET latency = {} WHERE IP = '{}';".format(average,IP[i])
                cursor.execute(SQL_command)
                SQL_command = "UPDATE pinglatency SET faliure = {} WHERE IP = '{}';".format(err, IP[i])
                cursor.execute(SQL_command)
                connector.commit()
                cursor.close()

    if sec %2 == 1:
        print(end=' ')
    print('程式運行中...')
    time.sleep(1)


