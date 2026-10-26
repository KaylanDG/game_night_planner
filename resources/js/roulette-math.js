// Angles are in degrees, measured clockwise from the top of the wheel, where the pointer sits.
// Segment i covers [i * size, (i + 1) * size) on the unrotated wheel.

export function segmentSize(count) {
    return 360 / count;
}

/**
 * The rotation that brings segment `index` (its centre, shifted by `offset` degrees)
 * under the pointer, always moving forward from `current` by `turns` full rotations plus the remainder.
 */
export function landingRotation({ current, index, count, turns = 6, offset = 0 }) {
    const size = segmentSize(count);
    const target = -(index * size + size / 2 + offset);
    const remainder = ((((target - current) % 360) + 360) % 360);

    return current + turns * 360 + remainder;
}

/**
 * The segment under the pointer when the wheel is rotated by `rotation` degrees.
 */
export function segmentAtPointer(rotation, count) {
    const angle = (((-rotation) % 360) + 360) % 360;

    return Math.floor(angle / segmentSize(count)) % count;
}

/**
 * A random landing spot inside the segment, kept well away from its edges,
 * so the wheel doesn't always stop dead centre.
 */
export function landingOffset(count, random = Math.random) {
    return (random() - 0.5) * segmentSize(count) * 0.7;
}
