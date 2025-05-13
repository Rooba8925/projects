import multiprocessing
import numpy as np
import time

def matrixMultiply(A, B):
    return np.matmul(A, B)

def parallelMatrixMultiply(A, B, num_processes):
    m, k = A.shape
    k, n = B.shape

    # Split matrices into chunks
    chunk_size = m // num_processes
    A_chunks = [A[i*chunk_size:(i+1)*chunk_size] for i in range(num_processes)]
    B_chunks = [B.copy() for _ in range(num_processes)]

    # Create a pool of worker processes
    pool = multiprocessing.Pool(processes=num_processes)
    results = pool.starmap(matrixMultiply, [(A_chunk, B_chunk) for A_chunk, B_chunk in zip(A_chunks, B_chunks)])
    pool.close()
    pool.join()

    # Concatenate the results
    C = np.vstack(results)

    return C

if __name__ == '__main__':
    m, k, n = 1000, 1000, 1000  # Adjust matrix dimensions as needed
    A = np.random.rand(m, k)
    B = np.random.rand(k, n)

    num_processes = multiprocessing.cpu_count()  # Get the number of available CPUs

    start_time = time.time()
    C_sequential = matrixMultiply(A, B)
    sequential_time = time.time() - start_time

    start_time = time.time()
    C_parallel = parallelMatrixMultiply(A, B, num_processes)
    parallel_time = time.time() - start_time

    print(f"Sequential matrix multiplication time: {sequential_time:.6f} seconds")
    print(f"Parallel matrix multiplication time: {parallel_time:.6f} seconds")

    # Verify the results
    assert np.allclose(C_sequential, C_parallel)
    print("Results are correct.")