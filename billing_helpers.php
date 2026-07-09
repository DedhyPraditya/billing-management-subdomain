<?php

function parse_billing_date(?string $date): ?DateTimeImmutable
{
    if (empty($date)) {
        return null;
    }

    return DateTimeImmutable::createFromFormat('Y-m-d', $date)
        ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date)
        ?: null;
}

function add_one_billing_month(DateTimeImmutable $date): DateTimeImmutable
{
    $year = (int) $date->format('Y');
    $month = (int) $date->format('n') + 1;
    $day = (int) $date->format('j');

    if ($month > 12) {
        $month = 1;
        $year++;
    }

    $lastDayOfTargetMonth = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
    $safeDay = min($day, $lastDayOfTargetMonth);

    return $date->setDate($year, $month, $safeDay);
}

function calculate_due_date_from_activation(?string $activatedAt): ?string
{
    $activationDate = parse_billing_date($activatedAt);
    if (!$activationDate) {
        return null;
    }

    return add_one_billing_month($activationDate)->format('Y-m-d');
}

function calculate_extended_due_date(?string $currentDue, ?DateTimeImmutable $today = null): string
{
    $today = $today ?: new DateTimeImmutable('now');
    $baseDate = $today;

    $parsedDue = parse_billing_date($currentDue);
    if ($parsedDue && $parsedDue > $today) {
        $baseDate = $parsedDue;
    }

    return add_one_billing_month($baseDate)->format('Y-m-d');
}
