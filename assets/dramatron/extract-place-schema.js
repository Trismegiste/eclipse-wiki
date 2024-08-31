/*
 * Eclipse Wiki
 */

export default {
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "type": "object",
    "properties": {
        "locations": {
            "description": "A list of locations mentionned in this fiction",
            "type": "array",
            "items": {
                "type": "object",
                "description": "A location extracted from this fiction",
                "properties": {
                    "name": {
                        "description": "The name of the location",
                        "type": "string"
                    },
                    "summary": {
                        "description": "A quick summary of the location in this fiction (optional)",
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
