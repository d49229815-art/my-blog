""""
li = [1,2,3,4]
# print(li)
li2 = li # 将li直接赋值给li2
print('li', li)
print("li2",li2)
print('li内存地址', id(li))
print("li2内存地址",id(li2))
li.append(5)  # 给li列表新增元素
print('新增后的li:', li)
print("新增后的li2:", li2)


import copy # 导入copy模块
li = [1, 2, 3, [4, 5, 6]]
li2 = copy.copy(li)
print('li', li)
print("li2",li2)
print('li内存地址', id(li))
print("li2内存地址",id(li2))
li.append(8)
li[3].append(7)         # 往嵌套列表添加元素
print('新增后的li:', li)
print("新增后的li2:", li2)

"""
import copy # 导入copy模块
li = [1, 2, 3, [4, 5, 6]]
li2 = copy.deepcopy(li)
print('li', li)
print("li2",li2)
print('li内存地址', id(li))
print("li2内存地址",id(li2))
li.append(8)            # 往第一层列表中添加元素，仅li添加此元素
li[3].append(7)         # 往嵌套列表添加元素,li与li2均会添加此元素
print('新增后的li:', li)
print("新增后的li2:", li2)


import builtins
print(dir(builtins))
print (abs(-10))
print (abs(10))
print (min(5,4,6))
print (max(5,4,6))
li=[1,2,3]
li2 = ['a', 'b', 'c']
print(zip(li, li2))
#第一种方式：通过for循环
for x in zip(li, li2):
    print(x)
    print(type(x))