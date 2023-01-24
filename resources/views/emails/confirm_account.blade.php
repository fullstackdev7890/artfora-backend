@extends('layouts.mail')

@section('title') Welcome email - ARTfora @endsection
@section('preheader') You're almost there, we just need you to verify your email address. @endsection

@section('content')
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center"
                style="font-size: 32px; font-family: Helvetica, Arial, sans-serif; color: #333333; padding-top: 30px;"
                class="padding-copy">Welcome {{$user['first_name']}}</td>
        </tr>
        <tr>
            <td align="left"
                style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;"
                class="padding-copy"
            >
                Thanks for signing up as a ARTfora partner, we're thrilled to have you on board.
                The first thing we need you to do is verify your email address by clicking on the button below:
            </td>
        </tr>
        <tr>
            <td align="center">
                <!-- BULLETPROOF BUTTON -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding-top: 25px;" class="padding">
                            <table border="0" cellspacing="0" cellpadding="0" class="mobile-button-container">
                                <tr>
                                    <td align="center" style="border-radius: 5px;" bgcolor="#329BFB">
                                        <a
                                            href="{{config('app.frontend_url')}}/verify-email?token={{ $hash }}&redirect={{ $redirect }}"
                                            target="_blank"
                                            style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; border-radius: 3px; padding: 15px 25px; border: 1px solid #329BFB; display: inline-block;"
                                            class="mobile-button">
                                            Verify email
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
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
