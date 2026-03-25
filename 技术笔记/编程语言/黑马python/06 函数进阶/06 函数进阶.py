
def func():
    # 就是返回了（1，2）的元组
    return 1, 2
x, y = func()
print(x, y)
print(type(x))
print(type(func()))
"""
输出结果：
    1 2
    <class 'int'>
    <class 'tuple'>
"""

def user_info(name, age, gender):
    print(f"您的名字是{name}, 年龄是{age}, 性别是{gender}")
user_info("Tom", 20, "男")
user_info(age=20, name="Tom", gender="男")   #输出结果：您的名字是Tom, 年龄是20, 性别是男
# 可以和位置参数混用，位置参数必须在前，且匹配参数顺序
user_info("Tom", gender="男", age=20)


def user_info(name, age, gender = "男"):
    print(f"您的名字是{name}, 年龄是{age}, 性别是{gender}")
user_info("Tom", 20)   #输出结果：您的名字是Tom, 年龄是20, 性别是男
# 函数调用时，如果为缺省参数传值则修改默认参数值，否则使用这个默认值
user_info("Tom", 20, "女")


def func(name, *xxx):
    print(f"我们是{name}, 我们的成员有：", end="")
    for i in xxx:
        print(i, end=" ")
    print()
func("黑马", "周杰伦", "王力宏", "张学友", "刘德华")


def func(name, **kwargs):
    print(kwargs)
func("aa", id = 1, age = 11, gender = "男", addr = "深圳石岩")

# ========== 函数练习题 ==========
def func(name, age, *args, **kwargs):
    print(f"我是{name}，年龄{age}岁，我的爱好有:", end ="")
    for i in args:
        print(i, end=" ")
    print("我的其他信息:", end =" ")
    for i in kwargs:
        print(f"{i}:{kwargs[i]}", end=" ")
    print()
func("刘德华", 15, "唱", "跳", "rap", "篮球", tall=188)

def func(compute):
    result = compute(1, 2)
    print(result)
def compute(x, y):
    return x + y
func(compute)

func(lambda a, b: a+b)

