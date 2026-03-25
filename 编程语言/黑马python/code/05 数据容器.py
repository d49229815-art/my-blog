# ======== 列表(list) ========
lst = [[1, 2, 3],[4, 5, 6]]
print(lst[0][0])    #结果为1
print(lst[-2][-3])  #结果为1

my_list = ["itheima", 'itcast', 'anson']
print(my_list.index('itheima'))     #查询itheima在my_list中的下标
my_list[0] = 'heima'                #修改my_list中的itheima为heima
my_list.insert(1,"heima")           #在my_list中itheima后插入heima
my_list.append("heima")             #在my_list末尾添加heima，仅追加一个
my_list.extend([4, 5, 6])           #将4，5，6以此追加到my_list末尾，可追加多个
del my_list[0]                      #删除my_list中的itheima
my_list.pop(0)                      #删除my_list中的itheima,但可返回被删掉的元素
my_list.remove("itheima")           #删除my_list中itheima的第一个匹配项，即如有多个itheima，也只删除第一个
my_list.clear()                     #清空列表
my_list = []                        #清空列表
my_list.count("itheima")            #统计my_list中itheima的个数
len(my_list)                        #统计my_list中元素的个数

lst = [1, 2, 3, 4]
index = 0
while index < len(lst):
    print(lst[index])
    index += 1

for x in lst:
    print(x)


# ======== 元组(tuple) ========
"""
定义语法(元素1, 元素2, 元素3...)
"""
t = (1, 2, 3)
s = tuple()

t1 = ((1, 2, 3), (4, 5, 6))
print(t1[0][0])  #结果为1

my_tuple = ("itheima", 'itcast', 'anson')
print(my_tuple.index('itheima'))     #查询itheima在my_tuple中的下标
my_tuple.count('itheima')            #统计my_tuple中itheima出现的次数
len(my_tuple)                        #统计元组的长度

my_tuple = ("itheima", 'itcast', 'anson')

index = 0
while index < len(lst):
    print(lst[index])
    index += 1
for x in my_tuple:
    print(x)

# ======== 字符串(str) ========
my_str = "ansonkeji667"
print(my_str.index('n'))

my_str = "| 周杰伦 | 王力宏 | 刘德华 |"
my_str.index('力')                  #查询n在my_str中的下标
my_str.replace('|', "，")           #将my_str中的'|'修改为'，'，只返回修改后的新字符串，并未对原有字符串做修改
lst = my_str.split('|')             #按指定分隔符将字符串分割出多份，并返回新的list
my_str.strip()                      #去除字符串的前后空格和换行符，并返回新的字符串
my_str.strip("|")                   #去除字符串的前后指定的字符，并返回新的字符串
my_str.count('v')                   #统计my_str中'|'出现的次数
len(my_str)                         #统计my_str中的字符个数
for x in my_str:
    print(x)

# ======== 序列切片 ========
lst = [0, 1, 2, 3, 4, 5, 6, 7]
print(lst[::])                      #按序取出列表中所有元素
print(lst[::-1])                    #按反序取出列表中所有元素
print(lst[3:5:])                    #取出3，4两个元素


# ======== 集合(set) ========
my_set = set()              #定义空集合
my_set = {0, 1, 2, 3, 4, 5, 6, 7, 8, 9}
print(type(my_set))
my_set = {0, 1, 2, 3, 4}
set2 = {0, 7, 9, 10}
my_set.add(5)                   #添加一个元素，插入位置不确定
my_set.remove(4)                #移除指定元素
my_set.pop()                    #随机取出一个元素
my_set.clear()                  #清空集合
my_set.difference(set2)         #取差集并返回一个新的集合,(my_set有但set2没有的元素)
my_set.difference_update(set2)  #删除my_set中和set2相同的元素
my_set.union(set2)              #集合并集，返回新的集合
len(my_set)                     #统计元素数量
for x in my_set:
    print(x)

# ======== 字典(dict) ========
d1 = {"name": "Tom", "age": 18}
d2 = {}                             #定义空字典
d3 = set()                          #定义空字典

d1 = {"Tom":{"age": 18, "gender": "man"},
      "Wie":{"age": 12, "gender": "woman"},
      "Sea":{"age": 11, "gender": "woman"}
      }
print(d1["Tom"]["gender"])

d = {"name": "Tom", "age": 18}
d["name"] = "Tim"               #新增/修改元素, 若key存在则修改，key不存在则新增
d.pop["name"]                   #删除某个key,并返回其对应的value
d.clear()                       #清空字典
d.keys()                        #获取字典的全部key
len(d)                          #计算字典中键值对的数量

d1 = {"Tom":{"age": 18, "gender": "man"},
      "Wie":{"age": 12, "gender": "woman"},
      "Sea":{"age": 11, "gender": "woman"}
      }
for key in d1:
    print(f"{d1}:{d1[key]}")

employees_info = {  "王力宏":{ "department": "科技部", "wage": 3000, "level": 1},
                    "周杰伦":{ "department": "市场部", "wage": 5000, "level": 2},
                    "林俊杰":{ "department": "市场部", "wage": 7000, "level": 3},
                    "张学友":{ "department": "科技部", "wage": 4000, "level": 1},
                    "刘德华":{ "department": "市场部", "wage": 6000, "level": 2}
                  }
print("全体员工当前信息如下：")
print(employees_info)
print("全体员工级别为1的员工完成升值加薪操作，操作后：")
for employee in employees_info:
    if employees_info[employee]["level"] == 1:
        employees_info[employee]["level"] = 2
        employees_info[employee]["wage"] += 1000
print(employees_info)
