
"""
if 要判断的条件:
    条件成立时，要做的事情（仅在满足条件时执行）
else:
    条件不成立时，要做的事情（仅在不满足条件时执行）
"""
age = int(input("年龄："))

if age >= 18:
    print("成年")
else:
    print("未成年")

"""
if 要判断的条件1:
    条件1成立时，要做的事情（仅在满足条件1时执行）
elif 条件2:
    条件2成立时，要做的事情（仅在满足条件2时执行）
else :
    所有条件不成立时，要做的事情（仅在不满足所有条件时执行）
注：py语句中用缩进来表示归属关系，要求是4个空格
判断有顺序且互斥，顺序执行后满足其中一条条件就不会继续执行
"""
score = int(input("成绩："))
if score >= 90:
    print("优秀")
elif score >= 60:
    print("及格")
else:
    print("不及格")

name = input("请输入名字：")                    # input()会阻塞程序运行，直到得到输入内容
print("你的名字是：%s" %name)
print("欢迎来到黑马动物园")
height = int(input("请输入你的身高(cm)："))
if height > 120:
    print("您的身高超出120cm，游玩需要购票10元")
else:
    print("您的身高未超出120cm，可以免费游玩")
print("祝您游玩愉快")

# ======== if-else嵌套练习 ========
"""
案例需求：
定义一个数字（1~10，随机产生），通过3次判断来猜出来数字
案例要求：
1. 数字随机产生，范围1-10
2. 有3次机会猜测数字，通过3层嵌套判断实现
3. 每次猜不中，会提示大了或小了
提示，通过如下代码，可以定义一个变量num，变量内存储随机数字。
import random
num = random.randint(1，10)
"""
import random
num = random.randint(1,10)
user_num = int(input("第一次猜测数字："))
if user_num != num:
    if user_num > num:
        print("大了")
        user_num = int(input("第二次猜测数字："))
        if user_num != num:
            if user_num > num:
                print("大了")
                user_num = int(input("第三次猜测数字："))
                if user_num > num:
                    print("大了")
                    print("您的机会用完了")
                else:
                    print("小了")
                    print("您的机会用完了")
            else:
                print("小了")
                user_num = int(input("第三次猜测数字："))
                if user_num > num:
                    print("大了")
                    print("您的机会用完了")
                else:
                    print("小了")
                    print("您的机会用完了")
        else:
            print("您猜中了！")
    else:
        print("小了")
        user_num = int(input("第二次猜测数字："))
        if user_num != num:
            if user_num > num:
                print("大了")
                user_num = int(input("第三次猜测数字："))
                if user_num > num:
                    print("大了")
                    print("您的机会用完了")
                else:
                    print("小了")
                    print("您的机会用完了")
            else:
                print("小了")
                user_num = int(input("第三次猜测数字："))
                if user_num > num:
                    print("大了")
                    print("您的机会用完了")
                else:
                    print("小了")
                    print("您的机会用完了")
        else:
            print("您猜中了！")
else:
    print("您猜中了！")
