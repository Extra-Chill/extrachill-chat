/**
 * ToolCallsInfo Component
 *
 * Displays information about tools used during the conversation.
 */

const toolNames = {
	search_extrachill: 'Searched Extra Chill network',
	add_link_to_page: 'Added link to artist page',
};

export default function ToolCallsInfo( { toolCalls } ) {
	if ( ! toolCalls || toolCalls.length === 0 ) {
		return null;
	}

	return (
		<div className="ec-tool-calls-info">
			{ toolCalls.map( ( call, index ) => {
				const toolName = toolNames[ call.tool ] || call.tool;
				const params = call.parameters?.query || call.parameters?.url || JSON.stringify( call.parameters );

				return (
					<div key={ index } className="ec-tool-call">
						{ toolName }: <code>{ params }</code>
					</div>
				);
			} ) }
		</div>
	);
}
