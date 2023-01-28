@extends('layouts.mail')

@section('title') New commission request @endsection
@section('preheader') {{ $name }} sent commission request to {{ $user['username'] }}. @endsection

@section('content')
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center"
                style="font-size: 32px; font-family: Helvetica, Arial, sans-serif; color: #333333; padding-top: 30px;"
                class="padding-copy"
            >
                {{ $name }} sent commission request to {{ $user['username'] }}
            </td>
        </tr>
        <tr>
            <td align="left"
                style="padding: 20px; text-align: center; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;"
                class="padding-copy"
            >
                <b>Contact back email <a href="mailto:{{$email}}">{{$email}}</a></b>
            </td>
        </tr>
        <tr>
            <td align="left"
                style="padding: 20px 20px; text-align: center; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;"
                class="padding-copy"
            >
                {{ $message }}
            </td>
        </tr>
    </table>
@endsection

@section('footer')
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="left"
                style="padding: 20px 0 50px 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;"
                class="padding-copy">If you have any questions, feel free to reach out to our <a
                    href="mailto:hello@artfora.com">partner success team</a> – we're lighting quick at replying.
            </td>
        </tr>
    </table>
@endsection
