import {comp, Component, DefaultEntity} from "@intermesh/goui";

export class BookmarkIFrame extends Component {
	constructor(bookmark: DefaultEntity) {
		super();

		this.items.add(comp({
			tagName: "iframe",
			style: {
				height: "93vh",
				width: "100vw",
				border: "none"
			},
			attr: {
				src: bookmark.content,
				title: "test"
			}
		})
		)
	}
}