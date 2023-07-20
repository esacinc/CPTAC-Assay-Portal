<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
        <meta name="title" content="{{ page_title|e }}"/>
        <meta name="viewport" content="width=device-width, maximum-scale=1.0"/>
        <title>{{ page_title|e }} | Office of Cancer Clinical Proteomics Research</title>
        <script type="text/javascript">
        var replacementUrl = "{{ replacement_url }}", redirectUrl = "{{ redirect_url }}";
        
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, null, replacementUrl);
        } else if (window.location.replace) {
            window.location.replace(replacementUrl);
        }
        
        window.location.href = redirectUrl;
        </script>
    </head>
</html>