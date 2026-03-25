# print(61641/108039)
"""
f = open("README.md", "r", encoding="utf-8")
print(f.read(10))
print(f.readlines(100))
f.close()

f = open("README.md", "r", encoding="utf-8")
for line in f:
    print(line.strip())
f.close()

with open("README.md", "r", encoding="utf-8") as f:
    print(f.readline())

with open("word.txt", "r", encoding="utf-8") as f:
    lst = f.read()
    print(lst.count("itheima"))
num = 0
with open("word.txt", "r", encoding="utf-8") as a:
    for line in a.readlines():
        line = line.strip()
        for word in line.split(" "):
            if word == "itheima":
                num += 1
    print(f"{num} words in total")
f = open("word.txt", "a", encoding="utf-8")
f.write("\nitheima")
f.write("\nithe")
# f.flush()
f.close()
# with open("word.txt", "w") as f:
#     f.write("\nitheima")
# with open("word.txt", "r") as f:
#     lst = f.read()
#     print(lst.count("itheima"))
"""

# === 文件操作综合练习 ===
fr = open("bill.txt", "r", encoding="utf-8")
fw = open("bill.txt.bak", "w", encoding="utf-8")
for line in fr.readlines():
    line = line.strip()
    if "测试" == line.split(",")[4]:
        continue
    fw.write(line)
    fw.write("\n")
fr.close()
fw.close()
