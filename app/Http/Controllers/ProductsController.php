<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;

use App\Models\Product;
use App\Exceptions\InvalidRequestException;

use App\Services\CategoryService;

class ProductsController extends Controller
{
    public function index(Request $request, CategoryService $categoryService)
    {
		 // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
		$query->orWhereHas('skus', function ($query) use ($like) {
         	 $query->where(function ($q) use ($like) {
             	 $q->where('title', 'like', $like)
                 ->orWhere('description', 'like', $like);
            });
            });
            });
        }

	 // 如果有传入 category_id 字段，并且在数据库中有对应的类目
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            // 如果这是一个父类目
	    if ($category->is_directory) {
                // 则筛选出该父类目下所有子类目的商品
		$builder->whereHas('category',function ($query) use ($category) {
			$query->where('path', 'like', $category->path.$category->id.'-%');
		});
        } else {
		//如果不是一个父类目，则直接筛选此类目下的商品
		$builder->where('category_id',$category->id);
	}
}

        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

//        修复分页导致category丢失
//        $products = $builder->paginate(16);
        $products = $builder->paginate(16)->appends(request()->query());

       return view('products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
	     'category' => $category ?? null,
	     'categoryTree' => $categoryService->getCategoryTree(),
        ]);
    }
public function show(Product $product, Request $request)
{
    if (!$product->on_sale) {
        throw new InvalidRequestException('商品未上架');
    }

    // 加载 SKU 关联（如果模型没有默认加载的话）
    // 如果已经在模型全局作用域或控制器其他位置加载过，可省略此行
    $product->load('skus');

    $favored = false;
    if ($user = $request->user()) {
        $favored = boolval($user->favoriteProducts()->find($product->id));
    }

    return view('products.show', [
        'product'     => $product,
        'favored'     => $favored,
    ]);
}

  public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

	 public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }

	 public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }
}
