/*
 * SPA Dramatron
 */
import { Ollama } from 'ollama/browser';
import { Scenario } from 'dramatron/scenario';
import characterSchema from 'dramatron/extract-character-schema';

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
            return defaultPayload
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

        async extractCharacter(event) {
            let story = ''
            for (let k = 1; k <= 5; k++) {
                const key = 'act' + k
                story = story.concat(this.scenario[key] + "\n")
            }

            let payload = this.getDefaultPayload()
            payload.messages[1].content = this.getQuestionForCharacter(JSON.stringify(characterSchema, null, 4), story)
            await this.printAnswer(payload, 'characterOutput')
            // @todo Better handling bad json and bad format of json
            const extract = this.extractJsonBlock(this.scenario.characterOutput)
            this.scenario.character = []
            for (const npc of extract.characters) {
                this.scenario.character.push({name: npc.name})
            }
        },

        extractJsonBlock(answer) {
            // @todo answer could only contain one JSON without text nor json block
            const regex = /```json\n([^`]+)\n```/
            let match;
            // @todo Better handling bad json and bad format of json
            if ((match = regex.exec(answer)) !== null) {
                console.debug('Extract from json block in answer')
                return JSON.parse(match[1])
            } else {
                console.debug('Extract from answer')
                return JSON.parse(answer)
            }

            throw new Error(answer + ' does not contains JSON code')
        },

        async totalCreation() {
            // Reboot the scenario, just keeping the pitch
            let reboot = new Scenario()
            reboot.pitch = this.scenario.pitch
            this.scenario = reboot
            // regenerate the general story and the 5 acts
            for (let k = 0; k < 6; k++) {
                await this.generate()
            }
            // generate characters
            await this.extractCharacter() // this will bug since there is no event and no icon to animate
        }
    })