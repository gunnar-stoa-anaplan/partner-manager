<?php

namespace App\Enums;

enum StepType: string
{
    case TrainingCompleted = 'training_completed';
    case MetricThreshold = 'metric_threshold';
    case UsageThreshold = 'usage_threshold';
    case TimeElapsed = 'time_elapsed';
    case FeatureUsage = 'feature_usage';
    case DataPopulated = 'data_populated';
}
