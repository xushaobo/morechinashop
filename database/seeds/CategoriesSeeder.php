<?php

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name'     => '水质分析仪',
                'children' => [
                    [
                        'name'     => 'PH/ORP测量仪',
                        'children' => [
                            ['name' => 'inoLab® pH 7310实验室台式PH/ORP测试仪'],
                            ['name' => 'pH 7110实验室台式PH/ORP测试仪'],
                        ],
                    ],
                ],
                'children' => [
                    [
                        'name' =>   'ISE离子浓度测量仪',
                        'children' => [
                            ['name' => 'PH/ION 7320'],
                            ['name' => 'ISE离子选择电极'],
                        ],
                    ]
                ],
            ],
            [
                'name'     => '气体检测仪',
                'children' => [
                    [
                        'name'     => '气体监测控制器',
                        'children' => [
                            ['name' => 'MP24机架安装型气体报警控制器'],
                            ['name' => 'MP-204四通道气体报警控制器'],
                        ],
                    ],
                ],
                'children' => [
                    [
                        'name' =>   '定制型多气体检测仪',
                        'children' => [
                            ['name' => 'PH/ION 7320'],
                            ['name' => 'IQ-1000万用气体检测仪'],
                        ],
                    ]
                ],
            ],
        ];

        foreach ($categories as $data) {
            $this->createCategory($data);
        }
    }

    protected function createCategory($data, $parent = null)
    {
        // 创建一个新的类目对象
        $category = new Category(['name' => $data['name']]);
        // 如果有 children 字段则代表这是一个父类目
        $category->is_directory = isset($data['children']);
        // 如果有传入 $parent 参数，代表有父类目
        if (!is_null($parent)) {
            $category->parent()->associate($parent);
        }
        //  保存到数据库
        $category->save();
        // 如果有 children 字段并且 children 字段是一个数组
        if (isset($data['children']) && is_array($data['children'])) {
            // 遍历 children 字段
            foreach ($data['children'] as $child) {
                // 递归调用 createCategory 方法，第二个参数即为刚刚创建的类目
                $this->createCategory($child, $category);
            }
        }
    }
}
