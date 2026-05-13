Strapi AI translation costs are based on **AI credits**, which are included in the **Growth CMS plan** ($45/month). Here's what you need to know:

## Credit Allocation

- The Growth plan includes **1,000 credits/month** [[CMS Pricing](https://strapi.io/pricing-cms)]
- Overages are billed at **$1.50 per 100 additional credits** when your monthly allowance runs out [[Strapi AI](https://support.strapi.io/articles/1821143913-understanding-strapi-ai)]

## How Credits Are Calculated

There is **no fixed conversion** (e.g., "X words = Y credits"). Usage is calculated dynamically based on the total amount of text processed (source + translated output). [[AI Translation Credit Usage](https://support.strapi.io/articles/2817318284-ai-translation-credit-usage)]

Reference examples from Strapi's documentation:

| Content                    | Target Languages | Credits Used  |
| -------------------------- | ---------------- | ------------- |
| Short sentence (~9 words)  | Spanish (1)      | ~0.09 credits |
| Short sentence (~9 words)  | 5 languages      | ~0.16 credits |
| Long paragraph (~60 words) | 5 languages      | ~0.35 credits |

Key points:

- Credit usage **does not scale linearly** — translating to 5 languages costs ~78% more than 1 language (not 5×), because the source text is processed once.
- Longer content requires more credits, but not proportionally more.

## How AI Translation Works in Strapi

When you enable **AI-powered internationalization**, saving content in the default locale (English) automatically triggers translation to all other configured locales. [[Internationalization](https://docs.strapi.io/cms/features/internationalization#ai-powered-internationalization)]

To get a precise estimate for your specific batch of web pages, you'd need to test with a sample of your content and monitor credit usage in _Settings → Overview_ in the admin panel.

---

## Formula Derivation

The credit estimator uses a two-parameter linear model fitted separately for 1 language and 5 languages.

**credits(words) = A + B × words**

### Step 1 — Language Scaling Factor (K)

Both reference points use the same 9-word sentence:

- 9 words → 1 language = **0.09 credits**
- 9 words → 5 languages = **0.16 credits**

**K = 0.16 / 0.09 = 16/9 ≈ 1.778**

Translating to 5 languages costs 1.778× the 1-language cost — not 5× — because Strapi processes the source document only once.

### Step 2 — Derive Paragraph Cost for 1 Language

The reference gives the 60-word paragraph cost for 5 languages only:

- 60 words → 5 languages = **0.35 credits**

Applying the inverse of K to get the 1-language equivalent:

**60 words → 1 language ≈ 0.35 × (9/16) ≈ 0.197 credits**

### Step 3 — Linear Fit for 1 Language

Solve the system A₁ + B₁ × words = credits using two known points:

| Words | Credits |
|-------|---------|
| 9     | 0.09    |
| 60    | 0.197   |

(60 − 9) × B₁ = 0.197 − 0.09 = 0.107

**B₁ = 0.107 ÷ 51 ≈ 0.002098 credits/word**

**A₁ = 0.09 − 9 × 0.002098 ≈ 0.0711**

### Step 4 — Linear Fit for 5 Languages

Solve the system A₅ + B₅ × words = credits using two known points:

| Words | Credits |
|-------|---------|
| 9     | 0.16    |
| 60    | 0.35    |

(60 − 9) × B₅ = 0.35 − 0.16 = 0.19

**B₅ = 0.19 ÷ 51 ≈ 0.003725 credits/word**

**A₅ = 0.16 − 9 × 0.003725 ≈ 0.1265**

### Final Formulas

| Target      | Per-page formula                     |
| ----------- | ------------------------------------ |
| 1 language  | 0.0711 + 0.002098 × words            |
| 5 languages | 0.1265 + 0.003725 × words            |

Per-segment estimates use only the variable rate (the fixed overhead is a per-page cost, not per-segment):

| Target      | Marginal rate per segment |
| ----------- | ------------------------- |
| 1 language  | 0.002098 × words          |
| 5 languages | 0.003725 × words          |

---

Answer based on the following sources:
- [strapi.io/pricing-cms](https://strapi.io/pricing-cms)
- [Understanding Strapi AI](https://support.strapi.io/articles/1821143913-understanding-strapi-ai)
- [Internationalization](https://docs.strapi.io/cms/features/internationalization#ai-powered-internationalization)
- [AI Translation Credit Usage](https://support.strapi.io/articles/2817318284-ai-translation-credit-usage)
