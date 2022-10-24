<html>
    <head>
        <title>Ovid Entitlement Report</title>
        <!-- @wk/fundamentals CSS -->
        <link
            rel="stylesheet"
            href="files/css/fundamentals-all.min.css"
        />
        <!-- @wk/components CSS -->
        <link
            rel="stylesheet"
            href="files/css/components-all.min.css"
        />
        <link
        rel="stylesheet"
        href="files/css/ag-grid.css" />

        <link
        rel="stylesheet"
        href="files/css/ag-theme-alpine.css" />
        <style type="text/css">
            #main-container, .wk-table {margin-top:10px;}
            #excel-dl, #excel-spinner, #icondl-spinner, #qrcdl-spinner, #videodl-spinner, #reset-spinner {display:none}
            .ag-header-cell-label,.grid-cell-centered  {
                justify-content: center;
                text-align:center
            }
            .wk-field-choice-label {
                font-size:.875rem;
            }
            .fixed-footer {
                position:absolute;bottom:0;width:100%}
            }
            .supt-icon {max-width:100%}
            .wk-card-container-body {display:inline-block}
            .img-row {margin-bottom:5px;}
            .card-buttons {text-align:right}
        </style>
        <!--[if IE]><link rel="shortcut icon" href="https://cdn.wolterskluwer.io/wk/fundamentals/1.x.x/logo/assets/favicon.ico"><![endif]-->
        <link rel="apple-touch-icon-precomposed" href="files/img/apple-touch-icon.png">
        <link rel="icon" href="files/img/favicon.png">
    </head>
    <body>
    <div class="wk-header-wrapper">
    <header class="wk-banner" role="banner">
        <div class="wk-banner-container">
            <a
                class="wk-logo-container"
                href="javascript:void(0)"
                title="Go to Wolters Kluwer Product X home page"
            >
                <img
                    class="wk-logo wk-logo-medium"
                    src="https://cdn.wolterskluwer.io/wk/fundamentals/1.x.x/logo/assets/white-wheel-medium.svg"
                    alt="Go to Wolters Kluwer Product X home page"
                />
                <img
                    class="wk-logo wk-logo-small"
                    src="https://cdn.wolterskluwer.io/wk/fundamentals/1.x.x/logo/assets/wheel-small.svg"
                    alt="Go to Wolters Kluwer Product X home page"
                />
            </a>
            <div class="wk-banner-content">
                <div class="wk-banner-left-content">
                    <div class="wk-banner-product-name"><h1>Ovid Entitlement Report</h1></div>
                </div>
            </div>
        </div>
    </header>
</div>
<div class="grid-container">
<div class="wk-page-container" id="loading-container" style="margin-top:60px;">
<label for="docs-description">Please wait, loading report...</label>
<div class="wk-loading">
    <progress id="docs-description" class="wk-progress" max="100"
        >75&#37;</progress
    >
</div>
</div>
<div class="wk-col-12" style="text-align:right;margin-top:10px;" id="main-btn-container">
    <button type="button" class="wk-button wk-button-icon-right" id="excel-dl" style="margin-right:1rem;">
        Download to Excel <span title="file-excel" class="wk-icon-file-excel"></span> <span title="spinner" class="wk-icon-spinner wk-spin" id="excel-spinner"></span></button>
</div>

<div class="wk-col-12" id="main-container">

</div>
</div>
<footer class="wk-footer fixed-footer" role="contentinfo">
    <div class="wk-footer-main" role="navigation">
        <a class="wk-logo" href="javascript:void(0)"><img class="wk-logo-small" src="https://cdn.wolterskluwer.io/wk/fundamentals/1.x.x/logo/assets/small.svg" alt="Wolters Kluwer Product Name - Page anchor links to"><img class="wk-logo-medium" src="https://cdn.wolterskluwer.io/wk/fundamentals/1.x.x/logo/assets/medium.svg" alt="Wolters Kluwer Product Name - Page anchor links to"></a>
        <ul class="wk-footer-nav">
            <li class="wk-footer-item"><a class="wk-footer-link" href="javascript:void(0)">Disclaimer</a></li>
            <li class="wk-footer-item"><a class="wk-footer-link" href="javascript:void(0)">Privacy & Cookies</a></li>
            <li class="wk-footer-item"><a class="wk-footer-link" href="javascript:void(0)">GDPR</a></li>
            <li class="wk-footer-item"><a class="wk-footer-link" href="javascript:void(0)">Securities Act Statement</a></li>
        </ul>
        <p class="wk-footer-copyright">&copy; 2021 Wolters Kluwer. All Rights Reserved.</p>
    </div>
</footer>
<div class="card-template wk-col-3" style="display:none">
    <section class="wk-card-container">
    <div class="card-buttons"></div>
    <header class="wk-card-container-header">
        <h3>Card Heading</h3>
    </header>
    <div class="wk-card-container-body"></div>
</section>
</div>
        <!-- Es6 polyfill for IE 11 -->
        <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>

        <!-- Custom Elements polyfill -->
        <script src="https://unpkg.com/@webcomponents/custom-elements@1.2.4/src/native-shim.js"></script>

        <!-- For IE 11 -->
        <script>
            if (typeof customElements === 'undefined') {
                document.write(
                    '<script src="https://cdnjs.cloudflare.com/ajax/libs/custom-elements/1.2.1/custom-elements.min.js"><\/script>'
                );
            }
        </script>
        <!-- @wk/components JS -->
        <script src="https://cdn.wolterskluwer.io/wk/components/1.x.x/bundle.js"></script>
        <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
        <script src="files/scripts/jquery.js"></script>
        <script src="files/scripts/scripts.js?version=288"></script>
    </body>
</html>