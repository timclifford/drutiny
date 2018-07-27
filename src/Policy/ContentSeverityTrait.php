<?php
namespace Drutiny\Policy;

trait ContentSeverityTrait {

  /**
   * @bool Severity.
   */
  protected $severity = 0;

  public function setSeverity($sev = 0)
  {
    switch (TRUE) {
      case $sev === PolicyBase::SEVERITY_NONE:
      case $sev === 'none':
        $this->severity = PolicyBase::SEVERITY_NONE;
        break;

      case $sev === PolicyBase::SEVERITY_LOW:
      case $sev === 'low':
        $this->severity = PolicyBase::SEVERITY_LOW;
        break;

      case $sev === PolicyBase::SEVERITY_NORMAL:
      case $sev === 'normal':
      case $sev === 'medium':
        $this->severity = PolicyBase::SEVERITY_NORMAL;
        break;

      case $sev === PolicyBase::SEVERITY_HIGH:
      case $sev === 'high':
        $this->severity = PolicyBase::SEVERITY_HIGH;
        break;

      case $sev === PolicyBase::SEVERITY_CRITICAL:
      case $sev === 'critical':
        $this->severity = PolicyBase::SEVERITY_CRITICAL;
        break;

      default:
        throw new \Exception("Unknown severity level: $sev.");
    }
  }

  public function getSeverity()
  {
    return $this->severity;
  }

  public function getSeverityName()
  {

    switch (TRUE) {
      case $this->severity === PolicyBase::SEVERITY_NONE:
      case $this->severity === 'none':
      case $this->severity === NULL;
        return 'none';

      case $this->severity === PolicyBase::SEVERITY_LOW:
      case $this->severity === 'low':
        return 'low';

      case $this->severity === PolicyBase::SEVERITY_NORMAL:
      case $this->severity === 'normal':
      case $this->severity === 'medium':
        return 'medium';

      case $this->severity === PolicyBase::SEVERITY_HIGH:
      case $this->severity === 'high':
        return 'high';

      case $this->severity === PolicyBase::SEVERITY_CRITICAL:
      case $this->severity === 'critical':
        return 'critical';

      default:
        throw new \Exception("Unknown severity level: $sev.");
    }
  }
}
 ?>
