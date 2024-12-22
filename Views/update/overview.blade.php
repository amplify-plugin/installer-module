@extends('installer::layouts.master-update')

@section('title', trans('installer::installer_messages.updater.welcome.title'))
@section('container')
    <p class="paragraph text-center">{{ trans_choice('installer::installer_messages.updater.overview.message', $numberOfUpdatesPending, ['number' => $numberOfUpdatesPending]) }}</p>
    <div class="buttons">
        <a href="{{ route('updater.database') }}" class="button">{{ trans('installer::installer_messages.updater.overview.install_updates') }}</a>
    </div>
@stop
