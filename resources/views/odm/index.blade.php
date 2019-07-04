@extends('shop::base')

@section('aimeos_body')
    this is a test page.




   <p>
       其中 shop::base是模板页。在 aimeos-laravel目录下.

       <br/>

    <b>模板页也可以我们自己做。</b>

   <br/>

    shop::base, ::前面的是类似namespace, 是在指定的目录里去找文件。
                base是路径，是在指定namespace下 view的相对目录。用.替换 /。文件名必须.blade.php结尾

    没有namespace的从resource/view下找。
   </p>
@stop
