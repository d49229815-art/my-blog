

def say_hi():
    print("Hello World")

def wangwang():
    print("A汪汪")

def add(a,b):
    return print(a+b)

name = "周杰伦"
age = 11
height = 172.56

# 如果本文件被直接执行，则内置变量__name__会被赋值为__main__
# 如果本文件被import或from导入，则内置变量__name__会被赋值为文件名称
# __name__是Python内置变量，任何代码文件都有
# 我们可以通过读取这个变量的值，从而确定这个代码文件是：被人导入了还是被作为程序执行了
if __name__ == '__main__':
    wangwang()