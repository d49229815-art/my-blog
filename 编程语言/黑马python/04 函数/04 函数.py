
# def func(x, y):
#     """
#     函数说明
#     :param x：形参x的说明
#     :param y：形参y的说明
#     :return： 返回值的说明
#     """
#     函数体
#     return 返回值

def my_lef(data):
    const = 0
    for i in data:
        const += 1
    return const
name = 'ans'
print(my_lef(name))
def add(a, b):
    return a+b
print(add(5,9))



# - 实现一个黑马ATM的效果
#   - 默认余额为5000000元
#   - 程序启动后要求输入客户姓名
#   - 主菜单提示客户姓名的欢迎词，可以实现查询余额、存款、取款、退出四个功能
#   - 除退出外，执行完对应任务可以返回到主菜单
#   - 存款、取款后都应显示当前余额
#   - 客户选择退出或输入错误，程序会退出，否则一直运行
print("欢迎来到黑马ATM")
name = input("请输入您的名字：")
money = 5000000
withdrawal_num = None
def main_screen():
    # """
    # ATM案例的主菜单函数，提示用户做输入，并返回用户输入的数字
    # :return: 用户输入的数字
    # """
    print('-'*10 + "主菜单" + "-"*10)
    print(f"{name}，您好，欢迎来到ATM，请选择操作：")
    print("查询余额 [输入1]")
    print("存款    [输入2]")
    print("取款    [输入3]")
    print("退出    [输入4]")
    return int(input("请输入您的选择："))
def balance_inquiry(selcet):
    """
    查询用户银行余额
    :return: None
    """
    if selcet != 0:
        print("----------------查询余额-----------------")
    print(f"{name}，您好,您的余额剩余：{money}元")
def deposit():
    """
    向用户余额存钱
    :return: None
    """
    global money
    print('-'*10 + "存款" + "-"*10)
    num = int(input(f"{name}，您好，您要存入多少元（请输入整数）："))
    print(f"{name}，您好,您存款{num}元成功")
    money += num
    balance_inquiry(0)
def withdrawal():
    global money
    print('-'*10 + "取款" + "-"*10)
    num = int(input(f"{name}，您好，您要取出多少元（请输入整数）："))
    if money > num:
        money -= num
        print(f"{name}，您好,您取款{num}元成功")
    else:
        print(f"{name}，您好,您取款{num}元失败")
    balance_inquiry(0)
while True:
    selcet = main_screen()
    if selcet == 1:
        balance_inquiry(1)
    elif selcet == 2:
        deposit()
    elif selcet == 3:
        withdrawal()
    else:
        print("欢迎下次来临，再见！")
        break