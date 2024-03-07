from queue import Queue
import os, threading
from threading import Lock



class safe_queue:
    def __init__(self, maxLen):
        self.queue = Queue()
        self.lock = threading.Lock()
        self.maxLen = maxLen

    def q_put(self, data):
        # 確保queue長度不會超過queue長度
        self.lock.acquire()
        self.queue.put(data)
        if self.queue.qsize() > self.maxLen:
            self.queue.get()
        self.lock.release()

    def q_get(self):
        return self.queue.get()

    def q_acquire(self):
        self.lock.acquire()

    def q_release(self):
        self.lock.release()
    
    def q_size(self):
        self.queue.qsize()

    def show_queue(self):
        self.lock.acquire()
        try:
            result_info = ' '.join(str(item) for item in self.queue.queue)
        finally:
            self.lock.release()

        return result_info
        
    def calcu_average(self):
        self.lock.acquire()
        try:
            if self.queue.qsize() == 0:
                return None
            
            total_sum = sum(int(item) for item in self.queue.queue)
            average = total_sum / self.queue.qsize()
        finally:
            self.lock.release()

        return average