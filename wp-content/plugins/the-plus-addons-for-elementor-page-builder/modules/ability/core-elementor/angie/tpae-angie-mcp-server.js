/**
 * TPAE <-> Angie MCP server - bundle entry.
 *
 * Registers TPAE's curated abilities as tools with Elementor's in-editor Angie
 * assistant, routing each call back to the PHP REST bridge (class-tp-angie-bridge.php).
 * Uses the real @elementor/angie-sdk API: build an McpServer, registerTool for each
 * ability, then AngieMcpSdk.registerLocalServer(). Bundled with esbuild ->
 * tpae-angie-mcp-server.min.js.
 */

import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { AngieMcpSdk } from '@elementor/angie-sdk';
import { z } from 'zod/v3';

const REST_BASE = 'tpae/v1/angie/';

function angieSettings() {
	// Our own localized data (window.tpaeAngie) first; fall back to core's
	// wpApiSettings if it happens to be present.
	return ( typeof window !== 'undefined' && ( window.tpaeAngie || window.wpApiSettings ) ) || {};
}

async function post( path, body ) {
	const s = angieSettings();
	const res = await fetch( ( s.root || '/wp-json/' ) + path, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': s.nonce || '' },
		credentials: 'same-origin',
		body: JSON.stringify( body || {} ),
	} );
	return res.json();
}

/** One JSON-Schema property -> a Zod type (registerTool wants a Zod raw shape). */
function propToZod( p ) {
	const t = ( p && p.type ) || 'string';
	let zt;
	if ( t === 'number' || t === 'integer' ) {
		zt = z.number();
	} else if ( t === 'boolean' ) {
		zt = z.boolean();
	} else if ( t === 'array' ) {
		zt = z.array( z.any() );
	} else if ( t === 'object' ) {
		zt = z.object( {} ).passthrough();
	} else {
		zt = z.string();
	}
	if ( p && p.description ) {
		zt = zt.describe( String( p.description ) );
	}
	return zt;
}

/** JSON-Schema object -> Zod raw shape { key: zodType }. */
function toZodShape( schema ) {
	const shape = {};
	if ( ! schema || schema.type !== 'object' || ! schema.properties ) {
		return shape;
	}
	const req = new Set( Array.isArray( schema.required ) ? schema.required : [] );
	for ( const [ key, prop ] of Object.entries( schema.properties ) ) {
		let zt = propToZod( prop );
		if ( ! req.has( key ) ) {
			zt = zt.optional();
		}
		shape[ key ] = zt;
	}
	return shape;
}

async function boot() {
	try {
		const abilities = await post( REST_BASE + 'abilities', {} );
		if ( ! Array.isArray( abilities ) || abilities.length === 0 ) {
			return;
		}

		const server = new McpServer( { name: 'the-plus-addons', version: '1.0.0' } );

		abilities.forEach( ( ability ) => {
			server.registerTool(
				ability.name,
				{
					title: ability.label,
					description: ability.description,
					inputSchema: toZodShape( ability.input_schema ),
				},
				async ( args ) => {
					const res = await post( REST_BASE + ability.name, args || {} );
					if ( res && res.error ) {
						return {
							content: [ { type: 'text', text: 'Error: ' + ( res.message || res.error ) } ],
							isError: true,
						};
					}
					return {
						content: [ { type: 'text', text: JSON.stringify( res && res.result !== undefined ? res.result : res ) } ],
					};
				}
			);
		} );

		const sdk = new AngieMcpSdk();
		await sdk.registerLocalServer( {
			name: 'the-plus-addons',
			title: 'The Plus Addons',
			version: '1.0.0',
			description: 'Build and edit Elementor pages with The Plus Addons widgets.',
			server,
		} );

		// eslint-disable-next-line no-console
		console.log( 'TPAE Angie: registered ' + abilities.length + ' tools' );
	} catch ( err ) {
		// eslint-disable-next-line no-console
		console.error( 'TPAE Angie: init failed', err );
	}
}

boot();
