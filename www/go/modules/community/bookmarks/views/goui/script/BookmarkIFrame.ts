import {comp, Component} from "@intermesh/goui";
import {Bookmark} from "@intermesh/community/bookmarks";

export class BookmarkIFrame extends Component {
	constructor(bookmark: Bookmark) {
		super();
		this.cls = "fit"

		this.items.add(
			comp({
				cls: 'fit',
				html: `<iframe src="${bookmark.content}" title="${bookmark.name}" width="100%" height="100%" style="border: none;" referrerpolicy="origin-when-cross-origin"></iframe>`
			})
		);
	}
}