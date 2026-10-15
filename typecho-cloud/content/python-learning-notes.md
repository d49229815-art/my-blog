<!--markdown-->
本文整理 Python 3.x 入门课程中的常用语法和示例，覆盖基础语法、分支、循环、函数、数据容器、文件、异常与模块。课程材料中的重复章节已合并到本页；示例按 Python 语法重新校订。

> 变量名绑定到对象；对象有类型。列表、字典和集合可以原地修改，字符串、数字和元组不可原地修改。元组如果包含列表，列表本身仍可以修改。

## 章节导航

- [一、基础语法](#py-basic-syntax)
- [二、逻辑判断](#py-conditions)
- [三、循环](#py-loops)
- [四、函数](#py-functions)
- [五、数据容器](#py-containers)
- [六、函数进阶](#py-function-advanced)
- [七、文件操作](#py-files)
- [八、异常、模块与包](#py-exceptions-modules)
- [参考资料](#py-references)

<a id="py-basic-syntax"></a>
## 一、基础语法

### 1. 字面量、变量与对象

字面量是直接写在代码里的值，例如整数、浮点数、字符串、布尔值和空值。赋值会让变量名指向一个对象；再次赋值会改变名称绑定的对象。

~~~python
count = 10
message = "hello"
count = "ten"  # count 现在绑定到字符串对象
~~~

变量名本身没有固定类型，对象有类型。需要在多个位置复用的值可以放进有意义的变量或常量名中。

~~~python
MAX_RETRIES = 3
print(type(MAX_RETRIES))  # <class 'int'>
~~~

### 2. 常见内置类型与可变性

| 类型 | 示例 | 可变性 |
| --- | --- | --- |
| int、float、complex、bool | 10、3.14、2j、True | 不可变 |
| NoneType | None | 不可变 |
| str | "Python" | 不可变 |
| tuple | (1, "a") | 元组本身不可变；其中的可变对象仍可修改 |
| list | [1, 2, 3] | 可变 |
| dict | {"name": "Tom"} | 可变 |
| set | {1, 2, 3} | 可变 |

可以用 type() 查看对象类型。bool 是 int 的子类，但编写普通业务代码时通常把它当作独立的布尔类型使用。

浮点数通常只能近似表示十进制小数，因此 0.1 + 0.2 不一定精确等于 0.3。比较计算结果时可使用误差范围；需要十进制精确运算时可了解 decimal.Decimal。

~~~python
print(type(123))
print(type(True))
~~~

### 3. 类型转换与真值判断

常见转换函数有 int()、float()、str() 和 bool()。转换是否成功取决于输入内容；例如 int("abc") 会抛出 ValueError。

~~~python
age = int("18")
price = float("19.9")
text = str(100)
~~~

在 if 和 while 条件中，None、False、数字零，以及空字符串、空列表、空元组、空字典和空集合的真值为假；大多数其他对象的真值为真。bool("False") 的结果是 True，因为这个字符串不是空字符串。

~~~python
bool(0)       # False
bool("")      # False
bool([])      # False
bool("False") # True
~~~

### 4. 运算符

#### 算术运算符

| 运算符 | 含义 | 示例 | 结果 |
| --- | --- | --- | --- |
| +、-、* | 加、减、乘 | 5 + 3 | 8 |
| / | 真除法 | 10 / 3 | 约 3.333 |
| // | 向下取整除法 | 10 // 3 | 3 |
| % | 取余 | 10 % 3 | 1 |
| ** | 幂 | 2 ** 3 | 8 |

// 是向负无穷方向取整，因此 -7 // 3 的结果是 -3。

#### 比较与逻辑运算符

比较运算符包括 ==、!=、<、<=、>、>=，结果是 True 或 False。== 比较值是否相等；is 比较两个名称是否指向同一个对象，初学时不要把二者混用。

逻辑运算符必须使用小写的 and、or、not。and 和 or 会短路求值；它们返回操作数之一，不保证返回布尔值。

~~~python
age = 20
has_ticket = True

print(age >= 18 and has_ticket)
print(not has_ticket)
~~~

#### 赋值

~~~python
x = 10
x += 5       # 等价于 x = x + 5
x //= 3
a = b = 0
left, right = 1, 2
left, right = right, left  # 交换两个名称绑定的值
~~~

### 5. 字符串

字符串是不可变的 Unicode 文本序列。可以用单引号、双引号或三引号创建字符串。

~~~python
single = 'hello'
double = "world"
multi_line = """第一行
第二行"""
~~~

常见转义包括 \n（换行）、\t（制表符）、\\（反斜杠）、\'（单引号）和 \"（双引号）。

字符串格式化可以使用 f-string、str.format() 或旧式 % 格式化；新代码通常优先使用 f-string。

~~~python
name = "Alice"
price = 19.99
print(f"{name} 的商品价格是 {price:.2f} 元")
print(f"{1_000_000:,}")  # 1,000,000
~~~

常用字符串操作：

~~~python
text = "  Python 基础  "
print(text.strip())             # 去除两端空白，返回新字符串
print(text.replace("基础", "入门"))
print("a,b,c".split(","))       # ['a', 'b', 'c']
print("banana".count("a"))
print(len("Python"))
~~~

### 6. 注释与文档字符串

井号开始单行注释。函数、类或模块的第一条字符串表达式可以作为文档字符串。

~~~python
# 计算两个数的和
def add(a, b):
    """返回 a 与 b 的和。"""
    return a + b
~~~

<a id="py-conditions"></a>
## 二、逻辑判断

input() 返回字符串。需要数字时先转换，并考虑输入不合法时可能出现的 ValueError。

~~~python
name = input("请输入名字：")
try:
    age = int(input("请输入年龄："))
except ValueError:
    print("年龄请输入整数")
else:
    if age >= 18:
        print(f"{name} 已成年")
    else:
        print(f"{name} 未成年")
~~~

if、elif、else 按顺序检查条件；遇到第一个为真的分支后，其余分支不会执行。条件之间不一定天然互斥，判断顺序会影响结果。

~~~python
score = 85

if score >= 90:
    print("优秀")
elif score >= 60:
    print("及格")
else:
    print("不及格")
~~~

Python 用缩进表示代码块。通常使用 4 个空格，并在同一个代码块中保持一致。

<a id="py-loops"></a>
## 三、循环

### 1. while

while 在条件为真时重复执行。需要有限次循环时，确保循环中的状态会改变，最终能够离开循环。

~~~python
count = 0
while count < 5:
    print(count)
    count += 1
~~~

### 2. for 与 range

for 用于逐个处理可迭代对象中的元素，例如字符串、列表、元组或 range。range(start, stop, step) 生成整数序列，包含 start、不包含 stop。

~~~python
for number in range(1, 5):
    print(number)  # 1、2、3、4
~~~

### 3. break 与 continue

break 结束最近一层 for 或 while 循环；continue 跳过当前这一次迭代，进入最近一层循环的下一次迭代。

~~~python
for number in range(1, 6):
    if number == 2:
        continue
    if number == 5:
        break
    print(number)
~~~

<a id="py-functions"></a>
## 四、函数

### 1. 定义、调用与返回值

函数把一段操作组织成可复用的代码。return 将结果交回调用方；执行到函数末尾而没有 return 时，返回 None。

~~~python
def add(a, b):
    """返回两个数的和。"""
    return a + b

result = add(2, 3)
print(result)  # 5
~~~

函数可以有多个 return；执行到一个 return 后，当前函数调用立即结束。

### 2. 作用域

函数内赋值的名称通常是局部名称。要在函数中重新绑定模块级名称，需要显式声明 global；更常见的做法是把状态作为参数传入并用返回值传出。

~~~python
def add_tax(price, rate):
    return price * (1 + rate)

total = add_tax(100, 0.06)
~~~

### 3. 文档字符串与参数

函数的文档字符串放在函数体第一行。普通参数可按位置或关键字传入；定义时有默认值的参数可以省略。

~~~python
def greet(name, greeting="你好"):
    """用指定问候语称呼某人。"""
    return f"{greeting}，{name}"

print(greet("小明"))
print(greet(name="小红", greeting="早上好"))
~~~

定义函数时，带默认值的参数应放在没有默认值的参数之后。不要把可变对象（如列表）直接用作默认值，因为默认值只在函数定义时创建一次。

~~~python
def add_item(item, items=None):
    if items is None:
        items = []
    items.append(item)
    return items
~~~

<a id="py-containers"></a>
## 五、数据容器

列表、元组、字符串是序列，可以按位置索引和切片。字典按键查找值；集合用于保存不重复元素和进行集合运算。

| 类型 | 可变 | 有序性 | 索引 | 常见用途 |
| --- | --- | --- | --- | --- |
| list | 可变 | 保持插入顺序 | 整数索引 | 需要增删改的序列 |
| tuple | 不可变容器 | 保持顺序 | 整数索引 | 固定结构的数据、打包多个返回值 |
| str | 不可变 | 保持字符顺序 | 整数索引 | 文本 |
| set | 可变 | 不保证顺序 | 不支持索引 | 去重、成员测试、集合运算 |
| dict | 可变 | 按插入顺序遍历（Python 3.7+） | 用键访问 | 键值映射 |

字典键和集合元素必须可哈希。常见的数字、字符串和仅包含可哈希元素的元组可以作为键；列表、字典和集合不能作为键。元组是否可哈希取决于它所包含的元素。

### 1. 列表 list

~~~python
numbers = [10, 20, 30]
print(numbers[0])      # 10
print(numbers[-1])     # 30
print(numbers[0:2])    # [10, 20]，结束位置不包含在结果中

numbers.append(40)
numbers.extend([50, 60])
numbers.insert(1, 15)
numbers[0] = 5
removed = numbers.pop()       # 删除并返回最后一个元素
numbers.remove(15)            # 删除第一个值为 15 的元素
print(numbers.count(20))
~~~

del 可按索引或切片删除元素；clear() 清空列表。用 for 元素 in 列表 遍历列表；需要同时取得索引时可用 enumerate()。

~~~python
for index, value in enumerate(numbers):
    print(index, value)
~~~

### 2. 元组 tuple

元组本身不可增删或替换元素；如果它包含列表等可变对象，该对象仍可被修改。单元素元组需要末尾逗号。

~~~python
point = (3, 4)
single = ("Python",)
x, y = point
print(x, y)
~~~

元组常用 count() 和 index()。多个值可以通过逗号打包为元组，return 1, 2 会返回一个二元组。

### 3. 字符串 str 与切片

字符串可以按索引读取，但不能通过索引直接修改字符。切片语法是 序列[start:stop:step]，stop 不包含在结果中；省略值时使用默认边界。

~~~python
text = "Python"
print(text[0])       # P
print(text[1:4])     # yth
print(text[::-1])    # nohtyP
~~~

### 4. 集合 set

集合不保留可依赖的元素顺序，不支持索引，并自动去重。空集合用 set() 创建；{} 创建的是空字典。set.pop() 移除并返回一个任意元素，不应当依赖具体返回哪一个。

~~~python
languages = {"C", "Python", "Python"}
other = {"Python", "Linux"}

languages.add("Rust")
languages.discard("C")  # 元素不存在时也不会报错
print(languages | other)  # 并集
print(languages & other)  # 交集
print(languages - other)  # 差集
~~~

remove() 在元素不存在时会抛出 KeyError；discard() 不会。集合支持 for 遍历，不支持按位置索引。

### 5. 字典 dict

字典中的键唯一；用已有键赋值会替换对应的值。空字典可以写成 {} 或 dict()。

~~~python
person = {"name": "Tom", "age": 18}
person["name"] = "Tim"
person["city"] = "上海"

age = person.get("age")          # 键不存在时默认返回 None
removed_age = person.pop("age")  # 删除键并返回对应的值
~~~

遍历键、值和键值对：

~~~python
scores = {"语文": 90, "数学": 95}

for subject in scores:
    print(subject, scores[subject])

for subject, score in scores.items():
    print(subject, score)
~~~

字典嵌套可用于表达分组数据。下面把所有等级为 1 的员工升一级并增加工资：

~~~python
employees = {
    "王力宏": {"department": "科技部", "wage": 3000, "level": 1},
    "周杰伦": {"department": "市场部", "wage": 5000, "level": 2},
    "张学友": {"department": "科技部", "wage": 4000, "level": 1},
}

for employee in employees.values():
    if employee["level"] == 1:
        employee["level"] += 1
        employee["wage"] += 1000
~~~

### 6. 容器通用操作

len() 返回元素数量。max() 和 min() 要求参与比较的元素之间可比较；对字典调用时比较的是键。list()、tuple()、set() 和 dict() 可用于创建或转换容器，转换规则取决于输入类型。set() 转换会去重。

sorted(iterable, reverse=True) 返回新列表，不会原地修改原容器。对字典排序时默认处理键。

~~~python
values = [3, 1, 2]
ordered = sorted(values)
descending = sorted(values, reverse=True)
~~~

<a id="py-function-advanced"></a>
## 六、函数进阶

### 1. 多返回值与解包

~~~python
def get_point():
    return 3, 4

x, y = get_point()
print(type(get_point()))  # <class 'tuple'>
~~~

### 2. 可变参数

*args 收集额外的位置参数为元组；**kwargs 收集额外的关键字参数为字典。

~~~python
def show_members(group, *members):
    print(f"{group} 的成员：")
    for member in members:
        print(member)

show_members("学习小组", "小林", "小周")
~~~

~~~python
def show_profile(**profile):
    print(profile)

show_profile(age=18, city="上海")
~~~

### 3. 函数作为参数与 lambda

函数也是对象，可以作为参数传给另一个函数。lambda 用于定义只包含一个表达式的匿名函数；复杂逻辑应使用 def。

~~~python
def apply(compute, left, right):
    return compute(left, right)

def add(left, right):
    return left + right

print(apply(add, 1, 2))
print(apply(lambda left, right: left + right, 1, 2))
~~~

### 4. ATM 练习

下面是简化的菜单练习。它用字符串读取选项，避免把菜单输入转换成整数；金额输入的错误处理可在掌握异常后继续补充。

~~~python
def main():
    name = input("请输入姓名：")
    balance = 5000

    while True:
        print(f"\n{name}，欢迎使用 ATM")
        print("1. 查询余额")
        print("2. 存款")
        print("3. 取款")
        print("4. 退出")
        choice = input("请选择：")

        if choice == "1":
            print(f"当前余额：{balance} 元")
        elif choice == "2":
            amount = int(input("存入金额："))
            if amount > 0:
                balance += amount
                print(f"存款成功，当前余额：{balance} 元")
            else:
                print("金额必须大于 0")
        elif choice == "3":
            amount = int(input("取出金额："))
            if 0 < amount <= balance:
                balance -= amount
                print(f"取款成功，当前余额：{balance} 元")
            else:
                print("金额无效或余额不足")
        elif choice == "4":
            print("再见")
            break
        else:
            print("无效选项，请重新选择")

if __name__ == "__main__":
    main()
~~~

<a id="py-files"></a>
## 七、文件操作

open() 打开文件。文本模式常用 r（读取）、w（写入并清空原内容）、a（追加）；在模式后加 b 表示二进制模式。文本文件应按实际编码明确指定 encoding。二进制模式读写 bytes，不能指定 encoding。

with 语句会在代码块结束时关闭文件，即使处理中途发生异常也会执行清理。

~~~python
with open("README.md", "r", encoding="utf-8") as file:
    for line in file:
        print(line.rstrip())
~~~

常用读取方法：

~~~python
with open("notes.txt", "r", encoding="utf-8") as file:
    all_text = file.read()       # 读取剩余全部内容
~~~

readline() 读取一行；readlines() 把剩余行一次性读入列表，大文件通常更适合逐行遍历。write() 写入字符串，并返回写入的字符数。

统计文件中某个单词出现次数的简化示例：

~~~python
with open("word.txt", "r", encoding="utf-8") as file:
    text = file.read()

print(text.count("itheima"))
~~~

<a id="py-exceptions-modules"></a>
## 八、异常、模块与包

### 1. 异常处理

语法错误通常在程序运行前被发现；异常是在运行过程中发生的错误。使用 try、except 处理预期内且能够恢复的异常。优先捕获具体异常；不要用空的 except 隐藏未知问题。

~~~python
try:
    age = int(input("年龄："))
except ValueError:
    print("请输入有效整数")
else:
    print(f"输入的年龄是 {age}")
finally:
    print("输入处理结束")
~~~

else 在 try 代码块没有异常时执行；finally 用于无论是否异常都需要执行的清理逻辑。未被当前 except 处理的异常会继续向调用方传播。

### 2. 模块

模块通常是包含 Python 代码的 .py 文件，可以定义函数、类和变量。常见导入方式：

~~~python
import math
from pathlib import Path
~~~

推荐明确导入需要的名称；from module import * 会把多个名称放入当前命名空间，容易产生冲突。

直接运行模块文件时，__name__ 的值为 "__main__"；模块被导入时，__name__ 通常是模块名。常见入口写法：

~~~python
def main():
    print("程序从入口运行")

if __name__ == "__main__":
    main()
~~~

### 3. 包

常规包通常是包含 __init__.py 的目录，里面可以再放模块和子包。现代 Python 也支持没有 __init__.py 的命名空间包；初学项目可先按常规包组织。

~~~text
my_package/
    __init__.py
    helpers.py
~~~

安装第三方包时，建议在当前 Python 环境中运行：

~~~bash
python -m pip install 包名
~~~

<a id="py-references"></a>
## 参考资料

- [Python 官方教程](https://docs.python.org/3/tutorial/)
- [内置类型参考](https://docs.python.org/3/library/stdtypes.html)
- [内置函数参考](https://docs.python.org/3/library/functions.html)
