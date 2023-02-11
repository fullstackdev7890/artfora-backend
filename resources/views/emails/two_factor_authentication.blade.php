@extends('layouts.mail')

@section('content')
    <table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0"
           style="box-sizing: border-box; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <!-- Body content -->
        <tr>
            <td align="center"
                style="padding: 0; font-size: 16px; line-height: 25px; font-family: 'Atkinson Hyperlegible', sans-serif; color: #c1c1c0;"
                class="padding-copy"
            >
                <h1 style="font-size: 20px;">Hello!</h1>
            </td>
        </tr>

        <tr>
            <td align="left"
                style="padding: 0; font-size: 16px; line-height: 25px; font-family: 'Atkinson Hyperlegible', sans-serif; color: #c1c1c0;"
                class="padding-copy"
            >
                Please enter this number to the form on the site:
            </td>
        </tr>

        <tr>
            <td align="center"
                style="padding: 0; font-size: 20px; font-weight: bold; line-height: 25px; font-family: 'Atkinson Hyperlegible', sans-serif; color: #c1c1c0;"
                class="padding-copy"
            >
                {{ $code }}
            </td>
        </tr>

        <tr>
            <td align="left"
                style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: 'Atkinson Hyperlegible', sans-serif; color: #c1c1c0;"
                class="padding-copy"
            >
                Regards,<br>
                <span style="font-family: Prozak, sans-serif; font-weight: 700; letter-spacing: 4px; color: #c1c1c0; font-size: 30px;">ARTfora</span>
            </td>
        </tr>
    </table>
@endsection