go.modules.community.comments.CommentLinkDetail = Ext.extend(go.detail.Panel, {
    entityStore: "Comment",

    stateId: 'co-comment-detail',

    relations: ["creator"],

    initComponent: function () {


        Ext.apply(this, {
            items: [{
                collapsible: true,
                title: t("Comment"),
                onLoad: function (detailView) {

                    let title = t('{author} wrote at {date}')
                        .replace('{author}', Ext.util.Format.htmlEncode(detailView.data.creator.displayName))
                        .replace('{date}', Ext.util.Format.date(detailView.data.createdAt,go.User.dateTimeFormat));

                    this.setTitle(Ext.util.Format.htmlEncode(title));
                    // this.items.itemAt(0).setText();
                },
                tpl: "<div class='s12 go-html-formatted'>{text:raw}</div>"

            }]
        });


        go.modules.community.comments.CommentLinkDetail.superclass.initComponent.call(this);


    }
});