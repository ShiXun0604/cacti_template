import os, time


def result_info(Latency, T):
    max_length = max(len(item) for item in Latency.keys())
    info_text = '懿成資訊，Cacti監測網頁延遲(WebLatency)\n-------------------監測結果-------------------\n'
    for i in Latency.keys():
        info_text += "{:<{width}} \t延遲時間: {} 毫秒\n".format(i, Latency[i], width=max_length)
    info_text += "-------------------監測結果-------------------\n上次監測時間:" + T
    return info_text


# 花式等待
def wait_and_clean(wait_time=20, clean_rate=10, wait_info='程式運行中...', break_min=None, longStay_info=''):
    a = int(wait_time/2)
    b = wait_time%2
    t = 0
    while True:
        if t == wait_time or (time.localtime().tm_min)%5==break_min:
            return
        if t%clean_rate == 0:
            os.system('cls' if os.name == 'nt' else 'clear')

            # 額外打印的東西
            print(longStay_info)   
        t += 1
        if t%2 == 0:
            print(wait_info)
        else:
            print(" "+wait_info)
        time.sleep(1)
  


