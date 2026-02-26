# Lead Assignment

> [!NOTE]
> Available to: PSM, Manager, Administrator (lead management). CoE (assignment rules configuration).

Lead Assignment handles the routing of new leads to the right partners. Field users manage individual leads, while CoE users configure the routing rules that drive automatic assignment.

## Field User View

### New Leads

Navigate to **Partner Management > Lead Assignment > New Leads** to see leads awaiting assignment. From here you can:

- Review lead details (contact info, source, requirements)
- Manually assign a lead to a partner
- Reject a lead with a reason

### Assigned Leads

View leads that have been matched to partners. Track whether the partner has acknowledged the lead and initiated follow-up.

### Rejected Leads

Review leads that were declined. Understanding rejection patterns helps the CoE refine routing rules and lead quality criteria.

## CoE: Assignment Rules

CoE users manage the routing rules that automate lead distribution. Navigate to **Strategy & CoE > Lead Assignment Rules**.

### Rule Types

| Rule Type | How It Works |
|-----------|-------------|
| **Geography** | Route leads to partners based on the lead's region, country, or territory |
| **Competency** | Match leads to partners with specific certifications or skill areas |
| **Load Balancing** | Distribute leads evenly across partners to prevent overloading |

### Creating a Rule

1. Navigate to **Lead Assignment Rules > Assignment Rules**
2. Click **Create Rule**
3. Select the rule type and configure matching criteria
4. Set priority (rules are evaluated in priority order)
5. Define the fallback behavior when no rule matches

### Rule Evaluation

When a new lead enters the system:

1. Rules are evaluated in priority order
2. The first matching rule determines the assignment
3. If no rules match, the lead remains unassigned for manual routing

## Assignment History

An audit trail records every assignment decision — who assigned the lead, when, which rule (if automatic), and the outcome. This history is useful for refining rules and measuring routing effectiveness.
