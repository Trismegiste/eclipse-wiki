/*
 * SPA Dramatron
 */
import { Ollama } from 'ollama/browser';
import { Scenario } from 'dramatron/scenario';
import characterSchema from 'dramatron/extract-character-schema';
import placeSchema from 'dramatron/extract-place-schema';

let defaultPayload = null
export function setDefaultPayload(payload) {
    defaultPayload = payload
    defaultPayload.options.num_ctx = 12000
}

export const factory = url => ({
        ollama: null,
        scenario: null,

        init() {
            this.scenario = new Scenario()
            this.ollama = new Ollama({host: url})
        },

        async generate() {
            const newGeneration = this.getStoryPayload()
            console.log('new generation for', newGeneration.field)
            await this.printAnswer(newGeneration.payload, newGeneration.field)
        },

        getDefaultPayload() {
            return JSON.parse(JSON.stringify(defaultPayload))
        },

        getStoryPayload() {
            if (!this.scenario.pitch) {
                throw new Error('Pitch is empty, cannot generate anything without, at least, a pitch')
            }

            let payload = this.getDefaultPayload()
            payload.messages[1].content = payload.messages[1].content
                    + this.scenario.pitch
                    + "\nDéveloppe ce synopsis en 5 actes"

            // adding generated story to the train-of-thought. If it's empty, returning the payload for generation
            if (this.scenario.story) {
                payload.messages[2] = {role: 'assistant', content: this.scenario.story}
            } else {
                return {field: 'story', payload: payload}
            }

            // Adding the already-generated/filled acts (skipping empty acts) to the train-of-thought
            for (let k = 1;
            k <= 5; k++) {
                if (this.scenario['act' + k]) {
                    payload.messages.push(this.getQuestionForAct(k))
                    payload.messages.push({role: 'assistant', content: this.scenario['act' + k]})
                }
            }

            // finding the first empty act and returning the payload with the question at the end
            for (let k = 1; k <= 5; k++) {
                const key = 'act' + k
                if (!this.scenario[key]) {
                    payload.messages.push(this.getQuestionForAct(k))
                    return {field: key, payload: payload}
                }
            }

            throw new Error('No empty field to ask the LLM to generate')
        },

        getQuestionForAct(n) {
            return {role: 'user', content: `Développe sur 5 scènes, en incluant une scène d'action, l'acte ${n} de ton synopsis`}
        },

        async printAnswer(payload, field) {
            // abort in any case
            this.ollama.abort()

            //re-init
            this.scenario[field] = ''
            this.$el.dispatchEvent(new CustomEvent('llm', {bubbles: true, detail: {running: true}}))
            const response = await this.ollama.chat(payload)

            try {
                for await (const part of response) {
                    this.scenario[field] = this.scenario[field].concat(part.message.content)
                    if (part.done === true) {
                        console.table({'prompt token count': part.prompt_eval_count, 'response token count': part.eval_count})
                    }
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.log('The request has been aborted')
                } else {
                    console.error('An error occurred:', error)
                }
            } finally {
                this.$el.dispatchEvent(new CustomEvent('llm', {bubbles: true, detail: {running: false}}))
            }
        },

        save() {
            localStorage.setItem('dramatron', JSON.stringify(this.scenario))
        },

        load() {
            this.scenario = JSON.parse(localStorage.getItem('dramatron'))
        },

        getQuestionForCharacter(schema, story) {
            // thanks to https://thoughtbot.com/blog/get-consistent-data-from-your-llm-with-json-schema for this :
            return `
Consider the following JSON Schema based on the 2020-12 specification:
\`\`\`json
${schema}
\`\`\`

This JSON Schema represents the format I want you to follow to generate your answer.
Now, generate a JSON object that will list all characters' names extracted from this story:
${story}

Based on all this information, generate a valid JSON object with list of characters' names. 
Optionaly, you can add the character's role if you can determine it.
`
        },

        getCompiledStory() {
            let story = ''
            for (let k = 1; k <= 5; k++) {
                const key = 'act' + k
                story = story.concat(this.scenario[key] + "\n")
            }

            return story
        },

        async extractCharacter() {
            let payload = this.getDefaultPayload()
            payload.messages[1].content = this.getQuestionForCharacter(JSON.stringify(characterSchema, null, 4), this.getCompiledStory())
            await this.printAnswer(payload, 'characterOutput')

            try {
                const extract = this.extractJsonBlock(this.scenario.characterOutput)
                this.scenario.character = extract.characters
            } catch (ex) {
                console.debug(this.scenario.characterOutput)
                throw new Error('Cannot parse the extracted characters JSON')
            }
        },

        extractJsonBlock(answer) {
            const regex = /```json\n([^`]+)\n```/
            let match;
            // if there is a json block in markdown format
            if ((match = regex.exec(answer)) !== null) {
                console.debug('Parse the json block in answer')
                return JSON.parse(match[1])
            } else {
                // try to parse the answer as a json only
                console.debug('Parse the answer')
                return JSON.parse(answer)
            }
        },

        async totalCreation() {
            this.ollama.abort()
            // Reboot the scenario, just keeping the pitch
            let reboot = new Scenario()
            reboot.pitch = this.scenario.pitch
            this.scenario = reboot
            // regenerate the general story and the 5 acts
            for (let k = 0; k < 6; k++) {
                await this.generate()
            }
            // generate characters
            await this.extractCharacter()
            // generate locations
            await this.extractPlace()
        },

        getQuestionForPlace(schema, story) {
            return `
Consider the following JSON Schema based on the 2020-12 specification:
\`\`\`json
${schema}
\`\`\`

This JSON Schema represents the format I want you to follow to generate your answer.
Now, generate a JSON object that will list all locations' names extracted from this story:
${story}

Based on all this information, generate a valid JSON object with list of locations' names. 
Optionaly, you can add a quick summary of the location if you can determine it.
`
        },

        async extractPlace() {
            let payload = this.getDefaultPayload()
            payload.messages[1].content = this.getQuestionForPlace(JSON.stringify(placeSchema, null, 4), this.getCompiledStory())
            await this.printAnswer(payload, 'placeOutput')

            try {
                const extract = this.extractJsonBlock(this.scenario.placeOutput)
                this.scenario.place = extract.locations
            } catch (ex) {
                console.debug(this.scenario.placeOutput)
                throw new Error('Cannot parse the extracted locations JSON')
            }
        }
    })