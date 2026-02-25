<?php

namespace SpykApp\FilamentPasswordlessLogin\Enums;

enum FilamentPasswordlessLoginActionPosition: string
{
    case EmailFieldHint = 'email_field_hint';
    case LoginFormEndButton = 'login_form_end_button';
}
