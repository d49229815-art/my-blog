
print(10)
print("hello")
print(True)
print(None)

# 单行注释
# 多行注释 / 文档字符串
"""
通常用于函数、类、模块说明
"""

# - 变量本身无类型
# - 数据对象有类型
# - 可反复赋值
x = 10
x = "hello"


#====== 变量练习 ======
money = 50
ice_cream = 10
cola = 5
print('当前钱包余额：',money,'元')
print('购买了冰激凌，花费：',ice_cream, '元')
print('购买了可乐，花费：',cola, '元')
print('最终，钱包剩余：',money - ice_cream - cola, '元')

print(type(123))
print(type(12.11))
print(type("anson"))

bool(0)
bool("")
bool([])


#====== 数据类型练习 ======
print('money数据类型:',type(money))

a_str = 'ansheng'
a_int = 50
a_float = 3.14
b_str = '安生'
b_int = 78
b_float = 18.999758
print(str(b_str) + a_str)
print(int(a_float) + int(b_float))
print(b_float)
A_str = 'ANSHENG'
print('a_str\nA_str')

#====== 字符串拼接联系 ======
money = input("请输入余额： ")
name = input("请输入名字： ")
salary = input("请输入工资： ")

message = "我是" + name + '，钱包有' + str(money) + '元，但是今天发了工资' + str(salary) + '元，目前钱包有' + str(int(money) + float(salary)) + '元'
message2 = "我是%s,钱包有%s元，但是今天发了工资%s元，目前钱包有%.2f元" % (name, money, salary, int(money)+float(salary))
message3 = f"我是{name},钱包有{money}元，但是今天发了工资{salary}元，目前钱包有{int(money)+float(salary):.2f}元"
print(message)
print(message2)
print(message3)

# 1. 拼接
name = "Alice"
greeting = "Hello, " + name + "!"
# 2. 重复
divider = "-" * 30  # "------------------------------"
# 3. 格式化（三种方式）
price = 19.99
quantity = 3

# 方式1: %格式化（旧式）
message = "单价: %.2f元，数量: %d，总价: %.2f元" % (price, quantity, price * quantity)
# 方式2: format方法（推荐）
message = "单价: {:.2f}元，数量: {}，总价: {:.2f}元".format(price, quantity, price * quantity)
# 方式3: f-string（Python 3.6+，最简洁）
message = f"单价: {price:.2f}元，数量: {quantity}，总价: {price * quantity:.2f}元"

# 格式化语法细节
# {变量:格式说明符}
#   :.2f  - 保留2位小数
#   :8d   - 宽度8，右对齐整数
#   :<10s - 宽度10，左对齐字符串
#   :^10s - 宽度10，居中对齐字符串
#   :,    - 千位分隔符（仅数字）
num = 1000000
print(f"{num:,}")  # 输出: 1,000,000

#====== 股价计算小程序练习题 ======
name = '传智播客'
stock_price = 19.99
stock_code = '003032'
stock_price_daily_growth_factor = 1.2
grow_days = 7

print(f"公司：{name}，股票代码：{stock_code}，当前股价：{stock_price} \n"
      "每日增长系数是：%2.1f, 经过%d天的增长后，股价达到了%4.2f元"
      %(stock_price_daily_growth_factor, grow_days,
        float(stock_price * stock_price_daily_growth_factor ** grow_days)))

name = input("你的名字是：")
print("你的名字是：%s" %name)
