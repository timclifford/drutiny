<?php

namespace Drutiny\Sandbox;


/**
 *
 */
trait ReportingPeriodTrait {

    /**
     * @var DateTime The begining of the reporting period.
     */
    protected $reportingPeriodStart;

    /**
     * @var DateTime The end of the reporting period.
     */
    protected $reportingPeriodEnd;

    public function setReportingPeriod(\DateTime $start, \DateTime $end)
    {
      return $this->setReportingPeriodStart($start)
                  ->setReportingPeriodEnd($end);
    }

    public function setReportingPeriodStart(\DateTime $start)
    {
      $this->reportingPeriodStart = $start;
      return $this;
    }

    public function setReportingPeriodEnd(\DateTime $end)
    {
      $this->reportingPeriodEnd = $end;
      return $this;
    }

    public function getReportingPeriodStart()
    {
      return $this->reportingPeriodStart;
    }

    public function getReportingPeriodEnd()
    {
      return $this->reportingPeriodEnd;
    }

    /**
     * @return DateInterval
     */
    public function getReportingPeriodInterval()
    {
      return $this->reportingPeriodEnd->diff($this->reportingPeriodStart);
    }

    public function getReportingPeriodDuration()
    {
      $interval = $this->getReportingPeriodInterval();
               // seconds
      $seconds = $interval->s
               // minutes to seconds
               + ($interval->i * 60)
               // hours to seconds
               + ($interval->h * 3600)
               // days to seconds
               + ($interval->d * 86400)
               // years to seconds
               + ($interval->y * 31536000);
     return $seconds;
    }

}
