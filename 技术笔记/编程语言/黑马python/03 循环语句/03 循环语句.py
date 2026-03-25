
"""
while 条件:
    条件成立时，要做的事情1
    条件成立时，要做的事情2
    …………
若想做到有限次数循环，有三个要素：
    1、循环控制因子（某个变量）
    2、循环控制条件（基于因子做判断）
    3、循环因子更新（修改因子的值，令因子可以在某一刻不满足条件）
"""
i = 0
while i < 5:
    print(i)
    i += 1

i = 1
a = 1
while i <= 100:
    a = a + i
    print(a)
    i += 1

print("内容", end='\n')
print("内容")
a = 1
while a < 10:
    b = 1
    while b <= a:
        print(f"{b}*{a}={a * b} ", end='\t')
        b += 1
    a += 1
    print()


# ======== for循环 ========
"""
for 临时变量 in 待处理数据集:
    循环条件满足时执行的代码
- range语句可以获取一个数字序列（可迭代类型的一种）
"""
for i in range(5):
    print(i)


name = "anson"
for x in name:
    print(x)
name = "itheima is a brand of itcast"
name_a_const = 0
for x in name:
    if x == "a":
        name_a_const += 1
print(f"{name}中共含有：{name_a_const}个字母a")
num = 100
x = 0
for i in range(1, num):
    if i % 2 == 0:
        x += 1
print(x)

# for循环实现每天表白一次，每次送十朵鲜花后说一句我喜欢你
for i in range(1,101):
    print(f"今天是我第{i}天表白祝我成功")
    for j in range(10):
        print(f"第{i}天送了第{j}朵鲜花")
    print("我喜欢你")

# for循环实现九九乘法表
for i in range(10):
    for j in range(i+1):
        if j > 0:
            print(f"{j}*{i}={i * j}", end = '\t')
    print()

for i in range(1,11):
    print(f"吃不吃第{i}碗饭，请输入吃或不吃")
    eat = input()
    if(eat == '不吃'):
        continue
    print("吃掉")
    # elif(eat == '不吃'):
        # continue