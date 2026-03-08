---
purpose: "Human-review gate for conversation reply drafts"
source: "Conversation report/*.md"
updated: "2026-03-08"
---

# Agent Reply Instructions (Human Approval Required)

## Operating Rules

- Always review the lead note and the full GHL conversation before sending.
- Treat `### Suggested Reply` as a draft only; edit for accuracy and tone.
- Do not send if the lead opted out (`STOP`, not interested, do-not-contact).
- If a message is legal/financial/dispute-sensitive, escalate to owner before sending.

## Triage Workflow

1. Open [[00 - Triage Dashboard]] and process `URGENT`, then `HOT`, then `WARM`.
2. Open each lead note and verify:
   - owner,
   - opportunity stage,
   - latest inbound/outbound direction,
   - unread count.
3. Approve, edit, or reject the suggested reply.
4. Record outcome in GHL (replied, no reply, follow-up date).

## Reply Quality Checklist

- Confirm names, scheduling details, and estimate context are correct.
- Keep replies short and actionable.
- Include one clear next step (time options, callback window, or confirmation).
- Avoid assumptions not present in the conversation.

## Refresh Schedule

- Reports auto-refresh every 6 hours via local cron.
- Script: `scripts/ghl_triage_report.py`
- Runner: `scripts/run_ghl_triage.sh`
- Log: `logs/ghl_triage.log`

---

# LIVE TRIAGE REPORT — 66 Unread Conversations

> Cross-referenced against 191 opportunities | Generated: 2026-03-08

---

## TIER 1 — RESPOND NOW (Revenue at Risk)

### 1. Anthony Eugene — $6,000 | Work Scheduled
- **Phone:** +14079230783 | **Owner:** Owner B
- **Message:** "Are they done cutting. Those trunk are quite high. And the neighbor on my right said your guys dropped stuff in her yard which they need cleaned up. Their name: Bryan and lauren"
- **Pipeline:** Work Scheduled — active job complaint
- **Action:** Call immediately. Verify crew status. Coordinate cleanup of neighbor Bryan & Lauren's yard ASAP.
- **Suggested Reply:** "Hi Anthony, I'm checking with the crew right now. We sincerely apologize about the debris — we'll get Bryan and Lauren's yard cleaned up today. I'll call you with an update within the hour."

### 2. Cristen Brown — $7,925 | Estimate Accepted
- **Phone:** +13213776303 | **Owner:** UNASSIGNED
- **Message:** "Sounds good. Thank you."
- **Action:** This is a CLOSE. Assign an owner. Send scheduling options. Move pipeline to "Work Scheduled."
- **Suggested Reply:** "Awesome, Cristen! We're excited to get started. What days work best for you? I'll get you locked in."

### 3. Eddie Muse — $3,500 + $36,000 | Estimate Accepted
- **Phone:** +14074620115 | **Owner:** Owner A
- **Message:** "Just keep me posted with your schedule. Thanks,"
- **Pipeline:** Estimate Accepted ($3,500) + Payment Collected ($36,000)
- **Action:** High-value repeat customer ($39,500 total). Respond with scheduling update immediately.
- **Suggested Reply:** "Hi Eddie, absolutely! We're looking at [dates] for your project. I'll confirm the exact date by end of day tomorrow."

### 4. Richard Swormar — $750 | Estimate Accepted
- **Phone:** +18147770688 | **Owner:** UNASSIGNED
- **Message:** "Any day but the 11th i have a Dr's appointment"
- **Action:** Ready to schedule. Avoid March 11th. Book and move to "Work Scheduled."
- **Suggested Reply:** "Perfect, Richard! We'll avoid the 11th. How about [date]? I'll get you on the calendar right away."

### 5. Marie Breen — $500 | Estimate Accepted
- **Phone:** +14077653698 | **Owner:** UNASSIGNED (4 unread)
- **Action:** Call back ASAP. 4 unread messages = repeated attempts to reach you.
- **Suggested Reply:** Call first. Then: "Hi Marie, so sorry we missed you! I'm available now — give me a call back or let me know a good time."

### 6. Cindy Fiacco — $500 | Payment Collected (Won)
- **Phone:** +18434756224 | **Owner:** Owner B
- **Message:** "When are you coming?"
- **Action:** Customer has PAID and is waiting for service. Respond with crew schedule immediately.
- **Suggested Reply:** "Hi Cindy! We have you scheduled for [date]. The crew will arrive between [time window]. I'll confirm the day before."

---

## TIER 2 — RESPOND TODAY (Active Opportunities)

### 7. Mireya Booher — $900 | Estimate Given
- **Owner:** Owner B
- **Message:** "Thank you for the estimate. I just need to submit the application to my hoa"
- **Action:** Offer HOA documentation support (scope of work letter, insurance cert).
- **Suggested Reply:** "Hi Mireya, absolutely! If you need any documentation for your HOA, just let me know and I'll send it right over."

### 8. Judy Caruthers — $8,445 | Estimate Given
- **Owner:** Owner A
- **Message:** "I like the mock ups, my nephew's are looking at everything including cost since the will be paying. My one nephew is saying to hold off for the markets to recover from Iran."
- **Action:** High value. Don't push — offer locked pricing for 30 days.
- **Suggested Reply:** "Hi Judy, glad you like the mockups! Just so you know, we can lock in this pricing for 30 days. Whenever your family is ready, we're here."

### 9. Tammy Ammerman — $5,244 | Estimate Given
- **Owner:** Owner A
- **Message:** "I did my husband is looking over will let you know something next week"
- **Action:** Set reminder to follow up next week if no response.
- **Suggested Reply:** "Sounds great, Tammy! Take your time. If either of you have questions, don't hesitate to reach out."

### 10. Howard Hartman — $1,080 | Follow Up Needed
- **Owner:** Owner B
- **Message:** "We are flexible. There was just a hiccup on our side."
- **Action:** Ready to reschedule. Offer new dates.
- **Suggested Reply:** "No worries at all, Howard! How about [2-3 date options]? Let me know what works."

### 11. Lay Hodge — $16,721 | Payment Collected (Won)
- **Owner:** UNASSIGNED
- **Message:** "We are now in the process of picking our contractors and should have an idea when things will begin by next week."
- **Action:** Major account. Stay top of mind. Assign an owner.
- **Suggested Reply:** "Great to hear, Lay! We're ready whenever you are. Just let us know."

### 12. Roberta & Claude Cromer — Estimate Scheduled
- **Owner:** Owner A
- **Message:** "We have to leave at 11:15 on March 10 to make the tour."
- **Action:** Appointment constraint. Acknowledge and plan to finish before 11:15 AM.
- **Suggested Reply:** "Got it! We'll wrap up well before 11:15 on the 10th. See you then!"

### 13. Elise Johnson — Estimate Scheduled
- **Owner:** Owner A
- **Message:** "Ok that's fine"
- **Action:** Confirmed. Send appointment details.
- **Suggested Reply:** "Great, Elise! You're all set. We'll see you [date/time]."

---

## TIER 3 — FOLLOW UP THIS WEEK

| Contact | Value | Message | Action |
|---------|-------|---------|--------|
| **Stephano Nati** | $1,500 | "My neighbor wants other quotes" | Don't push. Stay available. |
| **Brian Russell** | $0 | "Recovering from a stroke. Maybe June." | Be empathetic. Set June reminder. |
| **Mathew Cummings** | $1,210 | "Thank you! Have a good weekend." | Follow up Monday. |
| **Doug Severance** | $4,700 | "Thank you!" | Confirm next steps. |
| **Mell Leonard** | $2,200 | "Interested in an estimate" | Past customer. Schedule new estimate. |
| **Remy Colins** | $6,500 | "3 other projects first" | Set 60-day reminder. |
| **David Sherer** | $8,550 | "Comparing to other quotes" | Follow up tomorrow. |
| **Darcy Lee** | $1,575 | Outbound follow-up sent | Monitor for response. |
| **Chris Lamy** | $4,790 | Schedule filling up message sent | Monitor for response. |
| **Nancy Tallent** | $1,206 | Liked comparison message | Monitor. Assign owner. |

---

## TIER 4 — PIPELINE UPDATES NEEDED (No Reply Required)

| Contact | Message | Current Stage | Value | Update To |
|---------|---------|---------------|-------|-----------|
| **David Smith** | "I hired another company" | Follow Up Needed | $1,185 | **Lost/Not Interested** |
| **Aimee Valle** | "We went with someone else" | Estimate Declined | $850 | Close out |
| **Zinnia Maisonet** | "Stop" | Estimate Declined | $2,150 | **DND list + remove automations** |
| **Tharwat Habib** | "Stop" | Estimate Declined | $6,000 | **DND list + remove automations** |
| **Kathy Stroschein** | "No, thank you" | Estimate Declined | $788 | Close out |
| **Cindy Carbonell** | "No thanks." | Estimate Declined | $3,250 | Close out |
| **Claud Bowers** | "No Tks." | Lost/Not Interested | $0 | Already correct |
| **Cara Thompson** | "I think I'm ok" | Lost/Not Interested | $0 | Already correct |
| **Robert Allen** | "Health issues, hold everything" | Estimate Declined | $3,000 | **Follow Up Needed** (90-day) |
| **Henry Ellis** | "Great, thanks!" | Estimate Declined | $20,081 | **Review — positive on declined?** |
| **Lief Erickson** | "Thanks." (12 unread!) | Lost/Not Interested | $605 | **Review full history. 12 unread = red flag** |

---

## TIER 5 — MISSED CALLS / UNKNOWN CONTACTS

| Phone | Action |
|-------|--------|
| (407) 679-8238 | Monitor for reply to missed call text |
| (339) 218-8108 | Monitor for reply |
| (407) 885-8186 | Inbound call, no body. Try calling back. |
| (407) 300-2119 | Monitor for reply |
| (615) 681-5323 | Monitor for reply |
| (941) 565-4959 | Monitor for reply |

---

## CANCELLED APPOINTMENTS

| Contact | Stage | Action |
|---------|-------|--------|
| Kelisha Hart | Lost/Not Interested | Reschedule outbound sent. Monitor. |
| Colby Smith | Lost/Not Interested | Reschedule outbound sent. Monitor. |
| Phat Gagne | Lost/Not Interested | "See you soon" but stage = Lost. Verify. |
| Shirley Charyna | Lost/Not Interested | Cancelled + closed. No action. |
| Bryan Hanscom | Bad Leads | No action. |

---

## REVENUE AT RISK SUMMARY

| Tier | Count | Pipeline Value |
|------|-------|---------------|
| Tier 1 — Respond Now | 6 | **$54,675** |
| Tier 2 — Respond Today | 8 | **$32,390** |
| Tier 3 — This Week | 10 | **$32,231** |
| Tier 4 — Pipeline Updates | 11 | **$37,909** |
| **TOTAL** | **35** | **$157,205** |

---

## UNASSIGNED HIGH-VALUE — NEEDS OWNER NOW

| Contact | Value | Stage |
|---------|-------|-------|
| Cristen Brown | $7,925 | Estimate Accepted |
| Lay Hodge | $16,721 | Payment Collected |
| Henry Ellis | $20,081 | Estimate Declined (verify) |
| Nancy Tallent | $1,206 | Follow Up Needed |
| Richard Swormar | $750 | Estimate Accepted |
| Marie Breen | $500 | Estimate Accepted |
