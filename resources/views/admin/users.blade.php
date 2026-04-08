@extends('layouts.app')

@section('content')

<div class="main-content">

<h1>إدارة المستخدمين</h1>

<table class="table">

    <thead>
        <tr>
            <th>ID</th>
            <th>الاسم</th>
            <th>الإيميل</th>
            <th>الحالة</th>
            <th>التحكم</th>
        </tr>
    </thead>

    <tbody>
    @foreach($users as $u)
    <tr>
        <td>{{ $u->id }}</td>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>
            @if($u->is_active)
                <span style="color:green">فعال</span>
            @else
                <span style="color:red">موقوف</span>
            @endif
        </td>
        <td>
            <a href="{{ route('admin.toggle',$u->id) }}" class="btn">
                {{ $u->is_active ? 'تعطيل' : 'تفعيل' }}
            </a>
        </td>
    </tr>
    @endforeach
    </tbody>

</table>

</div>

@endsection
