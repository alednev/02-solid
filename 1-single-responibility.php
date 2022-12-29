<?php

// before

class SalesReporterOld
{
    public function between($startDate, $endDate)
    {
        if (!Auth::check()) {
            throw new Exception('Authentication required for reporting.');
        }

        $sales = $this->queryDBForSalesBetween($startDate, $endDate);

        return $this->format($sales);
    }

    protected function format($sales)
    {
        return "<h1>Sales: $sales</h1>";
    }
}

// using of old version

$report = new SalesReporterOld();

$begin = Carbon::now()->subDays(10);
$end = Carbon::now();

echo $report->between($begin, $end);

// after

interface SalesOutputInterface
{
    public function output($sales);
}

class HtmlOutput implements SalesOutputInterface
{
    public function output($sales)
    {
        return "<h1>Sales: $sales</h1>";
    }
}

class SalesRepository
{
    public function between($startDate, $endDate)
    {
        return DB::table('sales')->whereBetween('created_at', [$startDate, $endDate])->sum('charge') / 100;
    }
}

class SalesReporter
{
    private $repo;

    public function __construct(SalesRepository $repo)
    {
        $this->repo = $repo;
    }

    public function between($startDate, $endDate, SalesOutputInterface $formatter)
    {
        // all auth need to be at higher level of application (controller, for example)

        // extract all db to another class
        $sales = $this->repo->between($startDate, $endDate);

        $formatter->output($sales);
    }
}

// using of new version

$report = new SalesReporter(new SalesRepository);

$begin = Carbon::now()->subDays(10);
$end = Carbon::now();

echo $report->between($begin, $end, new HtmlOutput);
