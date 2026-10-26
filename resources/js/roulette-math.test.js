// Run with: npm run test:js
import assert from 'node:assert/strict';
import test from 'node:test';
import { landingOffset, landingRotation, segmentAtPointer } from './roulette-math.js';

test('the wheel stops on the backend-selected segment for any wheel size', () => {
    for (let count = 1; count <= 24; count++) {
        for (let index = 0; index < count; index++) {
            for (const current of [0, 37.5, 2160, -90, 7201.25]) {
                for (const random of [0, 0.25, 0.5, 0.999]) {
                    const offset = landingOffset(count, () => random);
                    const rotation = landingRotation({ current, index, count, offset });

                    assert.equal(segmentAtPointer(rotation, count), index, `count=${count} index=${index} current=${current} random=${random}`);
                }
            }
        }
    }
});

test('every spin moves forward by several full rotations', () => {
    for (const current of [0, 123, 4000]) {
        const rotation = landingRotation({ current, index: 2, count: 5, turns: 6 });

        assert.ok(rotation - current >= 6 * 360);
        assert.ok(rotation - current < 7 * 360);
    }
});

test('landing offsets stay inside the segment', () => {
    for (const count of [1, 2, 7, 30]) {
        const size = 360 / count;

        for (const random of [0, 0.5, 0.9999]) {
            assert.ok(Math.abs(landingOffset(count, () => random)) < size / 2);
        }
    }
});
