<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Foundation</title>
    @livewireStyles
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f1e8;
            --ink: #1f2937;
            --accent: #0f766e;
            --panel: rgba(255, 255, 255, 0.84);
            --border: rgba(15, 118, 110, 0.16);
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.16), transparent 28%),
                radial-gradient(circle at bottom right, rgba(180, 83, 9, 0.14), transparent 32%),
                var(--bg);
            color: var(--ink);
        }

        .platform-shell {
            max-width: 920px;
            margin: 0 auto;
            padding: 48px 20px 64px;
        }

        .hero,
        .stats,
        .notes {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .hero {
            padding: 32px;
            margin-bottom: 24px;
        }

        .eyebrow {
            margin: 0 0 12px;
            color: var(--accent);
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(2rem, 5vw, 3.25rem);
            line-height: 1;
        }

        .copy {
            margin: 0;
            max-width: 60ch;
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .stats article {
            padding: 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.8);
        }

        .stats span {
            display: block;
            font-size: 0.9rem;
            color: rgba(31, 41, 55, 0.72);
        }

        .stats strong {
            display: block;
            margin-top: 10px;
            font-size: 1.8rem;
        }

        .notes {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 24px;
            align-items: center;
        }

        .notes p {
            margin: 0;
            width: 100%;
            font-weight: 700;
        }

        .notes code {
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(15, 118, 110, 0.08);
            font-family: Consolas, Monaco, monospace;
        }
    </style>
</head>
<body>
    <livewire:platform-overview />
    @livewireScripts
</body>
</html>
