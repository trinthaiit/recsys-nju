
public class KNNWithEuclideanDistance{
	private double[][] user;
	private int selectedUser;
	private int k;
	private double[] similarity;
	private int[] KMinimun;
	
	public KNNWithEuclideanDistance(double[][] user, int selectedUser, int k){
		this.user = user;
		this.selectedUser = selectedUser;
		this.k = k;
	}
	
	public int[] compute(){
		similarity = new double[user.length];
		for(int i = 0; i < user.length; i++){
			double sum = 0;
			for(int j = 0; j < user[0].length; j++){
				sum += (user[i][j] - user[selectedUser][j]) * (user[i][j] - user[selectedUser][j]);
			}
			similarity[i] = Math.sqrt(sum);
	    }
		selectedKMinimun();
		return KMinimun;
	}
	
	private void selectedKMinimun(){
		int[] excludeSelectedUser = new int[user.length - 1];
		//exclude the selected user
		for(int i = 0; i < user.length - 1; i++){
			if(i >= selectedUser)
				excludeSelectedUser[i] = i + 1;
			else
				excludeSelectedUser[i] = i;
		}
		
		KMinimun = new int[k];
		for(int i =0; i < k; i++){
			KMinimun[i] = excludeSelectedUser[i];
		}
		
		//build the max heap
		for(int i = (k - 2) / 2; i >= 0; i--){
			percDown(i);
		}
		
		for(int i = k; i < excludeSelectedUser.length; i++){
			if(similarity[excludeSelectedUser[i]] < similarity[KMinimun[0]]){
				KMinimun[0] = excludeSelectedUser[i];
				percDown(0);
			}
		}
	}
	
	private void percDown(int i){
		int child;
		int tmp = KMinimun[i];
		for(; leftChild(i) < k; i = child){
			child = leftChild(i);
			if(child != k - 1 && similarity[KMinimun[child]] < similarity[KMinimun[child + 1]])
				child++;
			if(similarity[KMinimun[i]] < similarity[KMinimun[child]])
				KMinimun[i] = KMinimun[child];
			else break;
		}
		KMinimun[i] = tmp;
	}
	
	private int leftChild(int i){
		return 2 * i + 1;
	}
	
	public static void main(String[] args){
		int k = 2;
		double[][] user = {{103,999},{92,2},{8,4},{7,1000},{6,1},{22,0}};
		KNNWithEuclideanDistance knn = new KNNWithEuclideanDistance(user, 3, k);
		int[] result = knn.compute();
		System.out.println("The KNN with K = " + k + " is:");
		for(int i = 0; i < result.length; i++){
			System.out.println(result[i]);
		}
	}
}
	