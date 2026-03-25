"""
try:
    f = open("word.txt", "r", encoding="utf-8")
except FileNotFoundError as e:
    f = open("word.txt", "w", encoding="utf-8")
    print(e)
else:
    print("程序无异常")
finally:
    print("程序不知道有没有异常")
f.close()


def func1():
    print("hello, this is func1 start")
    2/0
    print("hello, this is func1 stop")
def func2():
    print("hello, this is func2 start")
    func1()
    print("hello, this is func2 start")
def main():
    try:
        func2()
    except Exception as e:
        print(e)
if __name__ == "__main__":
    main()


# 可理解为c语言中的 # include
import time
import time as t
import random

random.seed(0)
print(time.time())
t.sleep(2)  # 让程序睡眠5秒
print(t.time())


import A
A.say_hi()
A.wangwang()
A.add(A.height, A.age)


import A


from A import wangwang
from C import wangwang  #后导入的生效
wangwang()


from C import *
miaomiao()


import my_packet.modul1 as m1
from my_packet import modul1
import my_packet.modul2 as m2

# my_packet.modul1.say_hi()

m1.say_hi()

"""
print(75600 + 420 + 24)