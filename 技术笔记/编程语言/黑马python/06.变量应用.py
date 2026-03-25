
"""

from typing import List

from typing import List
class Solution:
    def twoSum(self, nums: List[int], target: int) -> List[int]:
        for i in range(len(nums)):
            for j in range(i + 1, len(nums)):
                if nums[i] + nums[j] == target:
                    return [i, j]
result = Solution().twoSum([2,7,11,15], 9)
if result:
    print(result[0], result[1])
else:
    print("没有找到符合条件的两个数")
"""