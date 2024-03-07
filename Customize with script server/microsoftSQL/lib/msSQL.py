import pymssql, time
from queue import Queue




global Data
Data = {}

def query_task(SQL_command, cursor, ip, job, DATA_COUNT):
    cursor.execute(SQL_command)  
    data = cursor.fetchall()[0][0]

     # 寫入全局變數中
    Data[ip][job].q_acquire()
    Data[ip][job].q_put(data)
    try:
        if Data[ip][job].q_size() > DATA_COUNT:
            Data[ip][job].q_get()
    except:
        pass
    Data[ip][job].q_release()


def fetch_task(MSSQL_SETTING, DATA_COUNT):
    # 連接到 SQL Server
    conn = pymssql.connect(**MSSQL_SETTING)
    cursor = conn.cursor()
    ip = MSSQL_SETTING['server']


    start_time = time.time()
    # -------- DB連線數 --------
    SQL_command = 'SELECT COUNT(client_net_address) AS CNT FROM sys.dm_exec_connections;'
    cursor.execute(SQL_command)  
    data = cursor.fetchall()[0][0]
    Data[ip]['conn_count'].q_put(data)
    

    # -------- 等待中的task數量 --------
    SQL_command = 'SELECT COUNT(*) FROM sys.dm_os_waiting_tasks;'
    cursor.execute(SQL_command)  
    data = cursor.fetchall()[0][0]
    Data[ip]['wait_count'].q_put(data)
    
    # -------- CPU用量 --------
    SQL_command = """
        with ResPoolCpu as
        (
            select instance_name as pool_name,
                counter_name,
                cntr_value
            from sys.dm_os_performance_counters
            where object_name like '%Resource Pool Stats%'
                and counter_name like 'CPU usage *% %' escape '*'
        )
        select *,
            convert
            (
                decimal(5,2),
                (rcp1.cntr_value * 1.0 / nullif(rcp2.cntr_value, 0))* 100
            ) as cpu_usage
        from ResPoolCpu rcp1
            inner join ResPoolCpu rcp2 on rcp1.pool_name = rcp2.pool_name
        where rcp1.counter_name not like '%base%' and rcp2.counter_name like '%base%';
    """
    cursor.execute(SQL_command)  # 回傳型態:[(),()], 第一個為default, 第二個為internal, tuple的第6向為結果值
    data1, data2 = cursor.fetchall()
    Data[ip]['default_cpu'].q_put(data1[6])
    Data[ip]['internal_cpu'].q_put(data2[6])


    # -------- 資料庫回應延遲 --------
    exec_time = (time.time() - start_time)*1000
    Data[ip]['delay_time'].q_put(exec_time)



    # 關閉游標和連接
    cursor.close()
    conn.close()