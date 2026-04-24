<?php

namespace App\Enums;

enum IssueCategory: string
{
    case Billing = 'billing';
    case Account = 'account';
    case Technical = 'technical';
    case Access = 'access';
    case GeneralInquiry = 'general_inquiry';
    case Feedback = 'feedback';
    case Other = 'other';
}
