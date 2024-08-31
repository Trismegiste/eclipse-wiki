/*
 * Eclipse Wiki
 */

export default {
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "type": "object",
    "properties": {
        "characters": {
            "description": "A list of characters mentionned in this fiction",
            "type": "array",
            "items": {
                "type": "object",
                "description": "A character extracted from this fiction",
                "properties": {
                    "name": {
                        "description": "Full name of the character",
                        "type": "string"
                    },
                    "role": {
                        "description": "Role of the character in this fiction (optional)",
                        "type": "string"
                    }
                },
                "required": [
                    "name"
                ],
                "additionalProperties": true
            }
        }
    },
    "required": [
        "characters"
    ],
    "additionalProperties": true
}
