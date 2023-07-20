$.when($.ready).then(function () {
    $("div.component-panel.component-link-panel > div").each(function (componentLinkPanelIndex, componentLinkPanelElem) {
        var componentLinkUrl = (componentLinkPanelElem = $(componentLinkPanelElem)).find("a[href]").attr("href");

        componentLinkPanelElem.click(function (event) {
            console.info(event);

            document.location.href = componentLinkUrl;
        });
    });
});
