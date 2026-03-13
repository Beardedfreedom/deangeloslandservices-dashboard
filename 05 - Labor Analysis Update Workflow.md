# Labor Analysis Update Workflow

Use this when you want to refresh the labor dashboard manually each week.

## Canonical Source

Edit this file only:

- `source/labor-analysis-data.json`

That file is now the manual source of truth for the labor dashboard.

## Publish Command

From the project root, run:

```bash
python3 "Landtree Autmation/scripts/publish_labor_analysis.py"
```

That command will:

1. Validate and copy `source/labor-analysis-data.json` into:
   - `templates/data/labor-analysis-data.json`
   - `wordpress/data/labor-analysis-data.json`
2. Regenerate:
   - `wordpress/labor-analysis.html`

## Optional Push

To publish immediately through the dashboard repo:

```bash
python3 "Landtree Autmation/scripts/publish_labor_analysis.py" --push
```

That will:

1. Sync the output files
2. Commit the labor-analysis changes in the dashboard repo
3. Push to `origin/main` for deployment

## Live Route

The labor dashboard page is served at:

- `/dashboard/labor/`

The manual labor snapshot JSON is served at:

- `/dashboard/data/labor-analysis-data.json`
