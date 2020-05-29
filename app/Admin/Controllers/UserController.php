<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

//        $grid->model()->where('id', '>', 5);
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->style('max-width:200px;word-break:break-all;');
        $grid->column('email', __('Email'))->copyable();
        $grid->column('created_at', __('Registration time'));
//        $grid->column('topics', __('topics count'))->display(function ($topics){
//            $count = count($topics);
//            return "<span class='label label-info'>{$count}</span>";
//        });
        $grid->column('topics')->pluck('title')->first();

        $grid->filter(function ($filter) {
//            $filter->expand();
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->between('created_at', '注册时间')->datetime();
            $filter->contains('name', '姓名');
            $filter->scope('trashed', '被软删除的数据')->onlyTrashed();

        });

        $grid->paginate(8);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('avatar', __('Avatar'));
        $show->field('introduction', __('Introduction'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('notification_count', __('Notification count'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('name', __('Name'));
        $form->email('email', __('Email'));
        $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        $form->password('password', __('Password'));
        $form->image('avatar', __('Avatar'));
        $form->text('introduction', __('Introduction'));
        $form->text('remember_token', __('Remember token'));
        $form->number('notification_count', __('Notification count'));

        return $form;
    }
}
