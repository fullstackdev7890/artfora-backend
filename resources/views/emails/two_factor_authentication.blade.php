@extends('layouts.mail')

@section('content')
    <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #FFFFFF; margin: 0 auto; padding: 0; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
        <!-- Body content -->
        <tr>
            <td class="content-cell"
                style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                <h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 19px; font-weight: bold; margin-top: 0; text-align: left;">
                    Hello!</h1>
                <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787E; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                    Please enter this number to the form on the site
                </p>
                <h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 32px; font-weight: bold; margin-top: 0; text-align: center;">
                    {{ $code }}
                </h1>
                <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787E; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
                    Regards,<br>{{config('app.name')}}</p>
            </td>
        </tr>
    </table>
@endsection